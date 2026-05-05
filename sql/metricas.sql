-- Total vías
select count(*) from via;

-- Total tramos de origen
select count(*) from rce_carrc;

    -- Total tramos importados
    select count(*) from tramo;

-- Número de tramos que no tienen pk
select count(*) from tramo where id not in (select id_tramo from pk);


-- Porcentaje de tramos que no tienen pk
select (select count(*) from tramo where id not in (select id_tramo from pk))*100/(select count(*) from tramo);


-- Simple, no optimizada, comprobación de que todos los tramos están bien orientados respecto a sus PK.
-- Si hay alguno negativo significa que está invertido.
with listapks as (
                select id_tramo, min(pk.pk) as minpk, max(pk.pk) as maxpk
                from pk
                group by id_tramo
                having count(*) > 1
            )
select
    st_linelocatepoint(st_linemerge(t.geom), (select geom from pk p where p.id_tramo = t.id and p.pk=maxpk)) -
    st_linelocatepoint(st_linemerge(t.geom), (select geom from pk p where p.id_tramo = t.id and p.pk=minpk)) as dif
from tramo t
join listapks on t.id = listapks.id_tramo
order by 1;

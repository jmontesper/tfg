-- Pruebas con los PK calibrados
-- Estadísticas globales. No ejecutar en el IDE, puede ser lento
with calc as (
    select
        c.carretera,
        c.pk as pk_real,
        p2pk(c.carretera, c.geom) as pk_estimado
    from rce_pkc c
)
select
    count(*) as n,
    avg(abs(pk_real - pk_estimado)) as error_medio,
    max(abs(pk_real - pk_estimado)) as error_max,
    percentile_cont(0.95) within group (order by abs(pk_real - pk_estimado)) as p95_error
from calc;


-- Visualizar
select
    c.carretera,
    c.pk as pk_real,
    p2pk(c.carretera, c.geom) as pk_estimado,
    c.pk - p2pk(c.carretera, c.geom) as error_absoluto
from rce_pkc c
where c.tipo_pk = 'k';


-- Función inversa: Visualización
select
    carretera,
    pk,
    geom,
    st_distance(geom, viapk2p((select id from via where via.denominacion = rce_pkc.carretera || ' [' || rce_pkc.id_carrete || ']'), rce_pkc.pk))
from rce_pkc
where rce_pkc.tipo_pk = 'v';


-- Función inversa: estadísticas
select
    tipo_pk,
    count(*) as n,
    avg(dist) as error_medio,
    max(dist) as error_max,
    percentile_cont(0.95) within group (order by dist) as p95_error
from (
    select
        r.tipo_pk,
        st_distance(
            r.geom,
            viapk2p(
                v.id,
                r.pk
            )
        ) as dist
    from rce_pkc r
    join via v
        on v.denominacion = r.carretera || ' [' || r.id_carrete || ']'
) s
group by tipo_pk;




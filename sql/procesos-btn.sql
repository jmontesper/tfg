-- Obtener duplicados en btn_carr
select nombre, count(distinct id_vial) from btn_carr
where nombre is not null and id_vial is not null
and nombre not ilike 'Red TEN-T%'
and nombre !~ '-'
group by nombre
having count(distinct id_vial) != 1
order by 2 desc;


-- Función para calcular los pk de los extremos de un tramo que "contiene" al menos un PK
drop function if exists tramopk;
create or replace function tramopk(tramo_id integer, tramo_geom geometry, num_pk integer) returns void as
$$
declare
    pk_ini_num  numeric(7, 3);
    pk_fin_num  numeric(7, 3);
    pk_cen_num  numeric(7, 3);
    pk_pri_num  numeric(7, 3);
    pk_ult_num  numeric(7, 3);
    pk_cen_pos  float8;
    pk_pri_pos  float8;
    pk_ult_pos  float8;
    pk_cen_geom geometry(point, 25830);
    pk_pri_geom geometry(point, 25830);
    pk_ult_geom geometry(point, 25830);
    long        float;
    tramo       geometry;
begin
    tramo := st_linemerge(tramo_geom);
    long := st_length(tramo);

    case
    when num_pk < 1 then
        return;
    when num_pk = 1 then
        -- Si solo tiene un PK
        select pk.pk, pk.geom
        into pk_cen_num, pk_cen_geom
        from pk
        where pk.id_tramo = tramo_id;
        pk_cen_pos := st_linelocatepoint(tramo, st_closestpoint(tramo, pk_cen_geom));
        pk_ini_num := pk_cen_num - (pk_cen_pos * long) / 1000;
        pk_fin_num := pk_cen_num + ((1 - pk_cen_pos) * long) / 1000;
    when num_pk > 1 then
        -- Calculo el PK del origen
        select pk.pk, pk.geom, st_linelocatepoint(tramo, st_closestpoint(tramo, pk.geom))
        into pk_pri_num, pk_pri_geom, pk_pri_pos
        from pk
        where pk.id_tramo = tramo_id
        order by st_linelocatepoint(tramo, st_closestpoint(tramo, pk.geom))
        limit 1;
        pk_ini_num := pk_pri_num - (pk_pri_pos * long) / 1000;
        -- Calculo el PK del final
        select pk.pk, pk.geom, st_linelocatepoint(tramo, st_closestpoint(tramo, pk.geom))
        into pk_ult_num, pk_ult_geom, pk_ult_pos
        from pk
        where pk.id_tramo = tramo_id
        order by st_linelocatepoint(tramo, st_closestpoint(tramo, pk.geom)) desc
        limit 1;
        pk_fin_num := pk_ult_num + ((1 - pk_ult_pos) * long) / 1000;
    end case;

    -- Si el PK inicial es mayor que el final, entonces hay que invertir el tramo y recalcular.
    if pk_ini_num > pk_fin_num then
        select st_linemerge(st_reverse(tramo_geom)) into tramo;
        if num_pk = 1 then
            pk_cen_pos := st_linelocatepoint(tramo, st_closestpoint(tramo, pk_cen_geom));
            pk_ini_num := pk_cen_num - (pk_cen_pos * long) / 1000;
            pk_fin_num := pk_cen_num + ((1 - pk_cen_pos) * long) / 1000;
        else
            pk_pri_pos := st_linelocatepoint(tramo, st_closestpoint(tramo, pk_pri_geom));
            pk_ult_pos := st_linelocatepoint(tramo, st_closestpoint(tramo, pk_ult_geom));
            pk_ini_num := pk_pri_num - (pk_pri_pos * long) / 1000;
            pk_fin_num := pk_ult_num + ((1 - pk_ult_pos) * long) / 1000;
        end if;
    end if;

    -- Actualizamos el tramo
    update tramo
    set pk_ini = pk_ini_num,
        pk_fin = pk_fin_num
    where tramo.id = tramo_id;
end;
$$ language plpgsql;


-- Cálculo de PK iniciales y finales de los tramos que tienen PK
do $$
declare
    r record;
begin
    for r in select id, geom, (select count(*) from pk p where p.id_tramo = t.id) as num from tramo t
    loop
        if r.num > 0 then
            perform tramopk(r.id, r.geom, r.num::int);
        end if;
    end loop;
end;
$$;






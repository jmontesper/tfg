create or replace function p2pk(codigo_busq text, punto_busq geometry) returns numeric as
$$
declare
    UMBRAL constant integer = 100;
    tramo_id        integer;
    tramo_geom      geometry;
    tramo_pk_ini    numeric(7, 3);
    tramo_pk_fin    numeric(7, 3);
    punto_pk_geom   geometry(point, 25830);
    punto_pk_pos    float8;
    pk_ant_num      numeric(7, 3);
    pk_ant_geom     geometry(point, 25830);
    pk_ant_pos      float8;
    pk_sig_num      numeric(7, 3);
    pk_sig_geom     geometry(point, 25830);
    pk_sig_pos      float8;
    resultado       numeric(7, 3);
begin
    -- I. Búsqueda del tramo más adecuado,
    -- que será el más cercano de entre las vías con el mismo código
    select t.id, st_linemerge(t.geom), t.pk_ini, t.pk_fin
    into tramo_id, tramo_geom, tramo_pk_ini, tramo_pk_fin
    from (
             -- Busco usando índice espacial (aproximado) y obtengo 10 candidatos, que es marginal
             select tramo.id, tramo.geom, tramo.pk_ini, tramo.pk_fin
             from tramo
                      join via on tramo.id_via = via.id
             where via.codigo = codigo_busq
             order by tramo.geom <-> punto_busq
             limit 10
         ) t
    -- Ordeno por distancias ya no aproximadas, sino exactas
    order by st_distance(t.geom, punto_busq)
    limit 1;
    -- Si la distancia supera el umbral, devolvemos null.
    if st_distance(tramo_geom, punto_busq) > UMBRAL then return null; end if;

    -- II. Obtengo el punto proyectado, o sea, el punto del tramo del que obtendremos su PK.
    select st_closestpoint(tramo_geom, punto_busq)
    into punto_pk_geom;

    -- III. Interpolación entre los dos PK adyacentes.
    -- Obtengo la posición lineal del punto proyectado
    select st_linelocatepoint(tramo_geom, punto_pk_geom)
    into punto_pk_pos;
    -- Busco el anterior PK por posicion.
    select p.pk, p.geom, p.pos
    into pk_ant_num, pk_ant_geom, pk_ant_pos
    from (
             select pk.pk, pk.geom, st_linelocatepoint(tramo_geom, st_closestpoint(tramo_geom, pk.geom)) as pos
             from pk
             where pk.id_tramo = tramo_id
             order by public.pk.geom <-> punto_pk_geom
             limit 10
         ) p
    where p.pos <= punto_pk_pos
    order by p.pos desc
    limit 1;
    -- Si no hay un anterior, recurrimos al extremo del tramo
    if not found then
        pk_ant_geom := st_startpoint(tramo_geom);
        pk_ant_pos := 0.0;
        pk_ant_num := tramo_pk_ini;
    end if;
    -- Busco el siguiente PK
    select p.pk, p.geom, p.pos
    into pk_sig_num, pk_sig_geom, pk_sig_pos
    from (
             select pk.pk, pk.geom, st_linelocatepoint(tramo_geom, st_closestpoint(tramo_geom, pk.geom)) as pos
             from pk
             where pk.id_tramo = tramo_id
             order by public.pk.geom <-> punto_pk_geom
             limit 10
         ) p
    where p.pos >= punto_pk_pos
    order by p.pos asc
    limit 1;
    -- Si no hay un posterior, recurrimos al extremo del tramo
    if not found then
        pk_sig_geom := st_endpoint(tramo_geom);
        pk_sig_pos := 1;
        pk_sig_num := tramo_pk_fin;
    end if;
    -- Calculamos
    -- Evitar división por cero, si los pk coinciden o son muy próximos (ya que están redondeados)
    if pk_sig_pos = pk_ant_pos then return pk_ant_num; end if;
    -- Calculamos el PK pedido
    resultado := pk_ant_num + ((punto_pk_pos - pk_ant_pos) / (pk_sig_pos - pk_ant_pos)) * (pk_sig_num - pk_ant_num);
    return resultado;
end;
$$ language plpgsql;

--------------------------------------------------------------------------------

drop function if exists p2viapk;
create or replace function p2viapk(punto_busq geometry)
    returns table (
        codigo text,
        pk numeric
)
as
$$
declare
    UMBRAL        CONSTANT integer = 100;
    tramo_geom    geometry;
begin
    -- Busco el tramo más cercano de entre todos los disponibles.
    select t.codigo, st_linemerge(t.geom)
    into codigo, tramo_geom
    from (
             -- Busco usando índice espacial (aproximado) y obtengo 10 candidatos, que es marginal
             select tramo.geom, via.codigo
             from tramo
                      join via on tramo.id_via = via.id
             order by tramo.geom <-> punto_busq
             limit 10
         ) t
    -- Ordeno por distancias ya no aproximadas, sino exactas
    order by st_distance(t.geom, punto_busq)
    limit 1;
    -- Si la distancia supera el umbral, devolvemos null.
    if st_distance(tramo_geom, punto_busq) > UMBRAL then
        return;
    end if;

    pk := p2pk(codigo, punto_busq);
    return next;
end;
$$ language plpgsql;

--------------------------------------------------------------------------------

drop function if exists viapk2p;
create or replace function viapk2p(via_id integer, pk_num numeric) returns geometry(point, 25830) as
$$
declare
    resultado geometry(point, 25830);
    tramo_id   integer;
    tramo_geom geometry;
    pk_ini     numeric(7,3);
    pk_fin     numeric(7,3);
    pk_ant    numeric(7,3);
    pk_sig    numeric(7,3);
    geom_ant  geometry;
    geom_sig  geometry;
    lant float8;
    incr float8;
begin
    -- Buscamos directamente un PK igual, ya que puede ocurrir.
    select p.geom
    into resultado
    from pk p
    join tramo t on p.id_tramo = t.id
    join via v on t.id_via = v.id
    where v.id = via_id
      and p.pk = pk_num;
    if found then
        return resultado;
    end if;
    -- Si no, buscamos un tramo que lo contenga.
    -- I. localizar tramo que contiene el PK
    select t.id, t.pk_ini, t.pk_fin, st_linemerge(t.geom)
    into tramo_id, pk_ini, pk_fin, tramo_geom
    from tramo t
    where t.id_via = via_id
      and pk_num between t.pk_ini and t.pk_fin
    limit 1;
    if not found then
        return null;
    end if;
    -- II. PK anterior
    select p.pk, p.geom
    into pk_ant, geom_ant
    from pk p
    where p.id_tramo = tramo_id
      and p.pk <= pk_num
    order by p.pk desc
    limit 1;
    -- Si no existe, vamos al inicio
    if not found then
        pk_ant := pk_ini;
        geom_ant := st_startpoint(tramo_geom);
    end if;
    -- III. PK siguiente
    select p.pk, p.geom
    into pk_sig, geom_sig
    from pk p
    where p.id_tramo = tramo_id
      and p.pk >= pk_num
    order by p.pk asc
    limit 1;
    -- Si no existe, vamos al final
    if not found then
        pk_sig := pk_fin;
        geom_sig := st_endpoint(tramo_geom);
    end if;
    -- Si son el mismo
    if pk_sig = pk_ant then
        return geom_ant;
    end if;
    -- IV. Calculamos por interpolación lineal
    lant := ST_LineLocatePoint(tramo_geom, geom_ant);
    incr := (ST_LineLocatePoint(tramo_geom, geom_sig) - lant) * (pk_num - pk_ant) / (pk_sig - pk_ant);
    if lant+incr > 1 then
        lant := 1;
        incr := 0;
    end if;
    return st_lineinterpolatepoint(tramo_geom, lant + incr);
end;
$$ language plpgsql;

--------------------------------------------------------------------------------

drop function if exists pk2p;
create or replace function pk2p(cod varchar, pk_num numeric) returns table (via_id integer, codcarr varchar, geom geometry(point, 25830)) as
$$
begin
    return query
    select
        via.id,
        via.codigo,
        viapk2p(via.id, pk_num)
    from via
    where via.codigo = cod
      and viapk2p(via.id, pk_num) is not null;
end;
$$ language plpgsql;

--------------------------------------------------------------------------------
-- Limpiamos todo
truncate table pk restart identity cascade;
truncate table tramo restart identity cascade;
truncate table via restart identity cascade;


-- Guardamos de momento el id_carrete como clave candidata. Insertamos el código que toque.
insert into via(denominacion, codigo)
select distinct  to_char(id_carrete, 'FM999999999999999'), upper(carretera)
from rce_carrc;


-- Guardamos los tramos calibrados relacionándolos con su via correspondiente
insert into tramo(id_via, geom, pk_ini, pk_fin)
select via.id, c.geom, c.pk_inicio, c.pk_fin
from rce_carrc c
join via on (via.denominacion = to_char(id_carrete, 'FM999999999999999') and via.codigo = c.carretera);



-- Guardamos los puntos kilométricos.
-- Es un poco más aparatoso porque hay que calcular a qué tramo pertenecen al vuelo,
-- en postgresql tenemos join lateral que es muy útil
insert into pk(id_tramo, pk, geom)
select t.id_tramo, p.pk, p.geom
from rce_pk p
join lateral
(
    select      tramo.id as id_tramo,
                st_distance(p.geom, tramo.geom) as distancia
    from        tramo join via on (tramo.id_via = via.id)
    where       via.denominacion = p.id_carrete
    order by    st_distance(p.geom, tramo.geom)
    limit       1
) t on true;


-- Por último modificamos la descripción
update via set denominacion = codigo || ' [' || denominacion || ']';

-- Veamos cuántas geometrias hay por tramo.
select st_numgeometries(geom) from tramo order by 1 desc;
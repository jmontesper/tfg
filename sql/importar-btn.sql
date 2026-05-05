-- Limpiamos todo
truncate table pk restart identity cascade;
truncate table tramo restart identity cascade;
truncate table via restart identity cascade;


-- Guardamos de momento el id_vial como clave candidata. Insertamos el código que toque.
-- Excluimos nulas y la ZP-2105 que está repetida por error, así como las de la Red TEN y las que no tienen codificación oficial.
insert into via(denominacion, codigo)
select distinct  to_char(id_vial, 'FM999999999999999'), upper(nombre)
from btn_carr
where id_vial is not null
  and nombre is not null
  and not (nombre = 'ZP-2105' and id_vial = 602000000446)
  and nombre not ilike 'Red TEN-T%'
  and nombre ~ '-';


-- Guardamos los tramos calibrados relacionándolos con su via correspondiente.
-- Será necesario transformar las geometrías ya que usan sistemas de referencia diferentes.
insert into tramo(id_via, geom)
select via.id, st_transform(c.geom, 25830)
from btn_carr c
join via on (via.denominacion = to_char(id_vial, 'FM999999999999999') and via.codigo = c.nombre);



-- Guardamos los puntos kilométricos.
-- Primero vamos a crear una columna adicional en el origen de datos para alojar las coordenadas transformadas
-- al sistema de referencia del modelo, y crearemos un índice espacial sobre ella.
alter table btn_pk drop column if exists geom2;
select AddGeometryColumn('public', 'btn_pk', 'geom2', 25830, 'POINT', 2);
update btn_pk set geom2 = st_transform(geom, 25830);
create index on btn_pk using GIST (geom2);


-- Volcar los PK es un poco más aparatoso porque hay que calcular a qué tramo pertenecen al vuelo,
-- en postgresql tenemos join lateral que es muy útil
insert into pk(id_tramo, pk, geom)
select t.id_tramo, p.pk_0618, p.geom2
from btn_pk p
join lateral
(
    select      tramo.id as id_tramo,
                st_distance(p.geom2, tramo.geom) as distancia
    from        tramo join via on (tramo.id_via = via.id)
    where       via.denominacion = to_char(p.id_vial, 'FM999999999999999')
    order by    st_distance(p.geom2, tramo.geom)
    limit       1
) t on true
-- Hay duplicados porque se pueden almacenar los pk de ambos sentidos. Se pueden ignorar.
on conflict do nothing;

-- Limpiamos columna
alter table btn_pk drop column if exists geom2;

-- Por último colocamos una mejor denominación a las vías
update via set denominacion = codigo || ' [' || denominacion || ']';

-- Veamos cuántas geometrias hay por tramo.
select st_numgeometries(geom) from tramo order by 1 desc;
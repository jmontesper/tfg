-----------------------------
-- Cámaras de tráfico Euskadi
----------------------
select road, pk, p2pk(road, geom) from camaras_trafico_euskadi where
p2pk(road, geom) is not null;


----------------------
-- Radares en Cataluña
----------------------
-- Adaptación de los datos:
select addgeometrycolumn('public', 'radares_cat', 'geom', 25830, 'point', 2);
create index on radares_cat using gist (geom);
select addgeometrycolumn('public', 'radares_cat', 'geom2', 25831, 'point', 2);
create index on radares_cat using gist (geom2);
update radares_cat set geom2 = st_setsrid(st_makepoint(x, y), 25831);
update radares_cat
set geom2 = null
where st_x(geom2) < 100000
   or st_x(geom2) > 800000
   or st_y(geom2) < 4000000
   or st_y(geom2) > 4850000;
update radares_cat
set geom = st_transform(geom2, 25830);

-- Prueba de validación
select
    r.via,
    r.pk1,
    p2pk(r.via, r.geom),
    abs(r.pk1 - p2pk(r.via, r.geom)) as error
from radares_cat r
where p2pk(r.via, r.geom) is not null
order by via, error desc;

-- Parece que hay un error de desplazamiento:
select
    r.via,
    r.pk1,
    p2pk(r.via, r.geom) as pk_calc,
    abs(r.pk1 - p2pk(r.via, r.geom)) as error,
    abs(r.pk1 - p2pk(r.via, r.geom))
        - avg(abs(r.pk1 - p2pk(r.via, r.geom))) over (partition by r.via)
        as error_corregido
from radares_cat r
where p2pk(r.via, r.geom) is not null
order by via, error_corregido;
-- Por los resultados también parece un error de escala, ya que el error varía simétricamente a partir de cierto PK como eje.
-- Descartamos los datos.


-------------------------
-- Tramos motos ---------
select m.carr, m.latini, m.longini, pkini, p2pk(m.carr, geom),
       abs(pkini-p2pk(m.carr, geom)),
pkfin, p2pk(m.carr, st_transform(ST_SETSRID(ST_MakePoint(m.longfin, m.latfin),4326), 25830)),
abs(pkfin-p2pk(m.carr, st_transform(ST_SETSRID(ST_MakePoint(m.longfin, m.latfin),4326), 25830)))
from motos m
where p2pk(m.carr, geom) is not null
order by carr

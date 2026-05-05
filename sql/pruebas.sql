-- Obtener el PK de un punto cercano a una vía y dos PK conocidos.
select p2pk('N-322', (select geom from test where id='01'));
-- Devuelve 365.846, correcto.

-- Obtener el PK de un punto lejano.
select p2pk('N-322', (select geom from test where id='02'));
-- Devuelve NULL, correcto.

-- Obtener el PK de un punto cercano a un tramo y un extremo inicial.
select p2pk('N-322', (select geom from test where id='03'));
-- Devuelve 193.904, correcto.

-- Obtener el PK de un punto cercano a un tramo y un extremo final.
select p2pk('N-322', (select geom from test where id='04'));
-- Devuelve 456,005, correcto

-- Obtener la vía y el PK de un punto cercano a una vía.
select p2viapk((select geom from test where id='05'));
-- Devuelve N-430, 501.092, correcto

-- Obtener la vía y el PK de un punto aislado.
select p2viapk( (select geom from test where id='06'));
-- Devuelve NULL, correcto.


-- Obtener el punto de un PK de cierta vía
insert into test (id, notas, geom)
select '07', 'Punto KM 95.50 de la N-330', (pk2p('N-330', 95.50)).geom;
-- Obtener el punto de un PK cercano al principio de cierta vía
insert into test (id, notas, geom)
select '08', 'Principio de N-330', (pk2p('N-330', 93.8)).geom;
-- Obtener el punto de un PK cercano al final de cierta vía
insert into test (id, notas, geom)
select '09', 'Final de N-330', (pk2p('N-330', 170.2)).geom;
-- Obtener el punto de un PK lejano a cierta vía por defecto
insert into test (id, notas, geom)
select '10', 'N-330, pk inexistente inicial', (pk2p('N-330', 80)).geom;
-- Obtener el punto de un PK lejano a cierta vía por exceso
insert into test (id, notas, geom)
select '11', 'N-330, pk inexistente final', (pk2p('N-330', 400)).geom;


-- Obtener los puntos de un PK de vía conocida con el mismo código
select pk2p('AP-7', 190.50);
-- Devuelve la tabla, correcto
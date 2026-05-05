-- Crear tabla donde almacenar los resultados de la prueba de rendimiento
create table if not exists rendimiento_p2pk
(
    id        serial primary key,
    n         integer,
    tiempo_ms double precision
);

-- Borrado si existía previamente
truncate table rendimiento_p2pk;

-- Prueba de rendimiento
do
$$
    declare
        n         integer;
        i         integer;
        tini      timestamp;
        tfin      timestamp;
        tiempo_ms double precision;
        suma      double precision;
    begin
        for n in 100..120 by 10
            loop
                -- Contador a 0
                suma := 0;
                -- Se repetirá 5 veces y se tomará la media
                for i in 1..5
                    loop
                        -- Registramos marca inicial
                        tini := clock_timestamp();
                        -- Convertimos puntos aleatorios desde el catálogo RCE calibrado
                        perform p2pk(carretera, geom)
                        from rce_pkc
                        order by random()
                        limit n;
                        -- Registramos marca final
                        tfin := clock_timestamp();
                        -- Acumulamos el tiempo transcurrido
                        suma := suma + extract(epoch from (tfin - tini)) * 1000;
                    end loop;
                -- Tiempo medio
                tiempo_ms := suma / 5;
                -- Guardamos el numero de conversiones y el tiempo medio
                insert into rendimiento_p2pk (n, tiempo_ms)
                values (n, tiempo_ms);
            end loop;
    end
$$;


-- Modelo lineal
select regr_slope(tiempo_ms, n) as a, regr_intercept(tiempo_ms, n) as b, regr_r2(tiempo_ms, n) as r2
from rendimiento_p2pk;


-- Tiempo medio por operación, desviación estándar y coeficiente de variación
select
    avg(tiempo_ms / n) as media,
    stddev(tiempo_ms / n) as desviacion,
    stddev(tiempo_ms / n) / avg(tiempo_ms / n) as coef_variacion
from rendimiento_p2pk;
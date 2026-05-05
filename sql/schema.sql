drop table if exists pk cascade;
drop table if exists tramo cascade;
drop table if exists via cascade;

create table via
(
    id           serial primary key,
    denominacion varchar(256) not null unique,
    codigo       varchar(256) not null
);
create index via_idx_codigo on via (codigo);


create table tramo
(
    id     serial primary key,
    id_via integer
        constraint tramo_rel_via references via (id) on update cascade on delete cascade not null,
    pk_ini numeric(7, 3),
    pk_fin numeric(7, 3)
);
create index on tramo (id_via);
select AddGeometryColumn('public', 'tramo', 'geom', 25830, 'MULTILINESTRING', 2);
create index on tramo using GIST (geom);


create table pk
(
    id_tramo integer
        constraint pk_rel_tramo references tramo (id) on update cascade on delete cascade,
    pk numeric(7, 3),
    primary key (id_tramo, pk)
);
select AddGeometryColumn('public', 'pk', 'geom', 25830, 'POINT', 2);
create index on pk using GIST (geom);

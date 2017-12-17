CREATE DATABASE IF NOT EXISTS curso_angular;
USE curso_angular;
CREATE TABLE productos(
  id int(255) auto_increment not null,
  nombre varchar(255),
  descripcion text,
  precio double(8,2),
  imagen varchar(255),
  CONSTRAINT pk_productos PRIMARY KEY(id)
)ENGINE=InnoDb;

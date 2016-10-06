/* @author Martin Borek (xborek08) <mborekcz@gmail.com> */
/*
SET FOREIGN_KEY_CHECKS=0;
DROP TABLE admin;
DROP TABLE postman;
DROP TABLE customer;
DROP TABLE publication;
DROP TABLE invoice;
DROP TABLE subscription;
SET FOREIGN_KEY_CHECKS=1;
*/

CREATE TABLE admin (
  admin_id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  login VARCHAR(50) NOT NULL UNIQUE,
  password CHAR(32) NOT NULL,
  name VARCHAR(50),
  surname VARCHAR(50),
  email VARCHAR(100)
);
  
CREATE TABLE postman (
  postman_id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  login VARCHAR(50) NOT NULL UNIQUE,
  password CHAR(32) NOT NULL,
  name VARCHAR(50) NOT NULL,
  surname VARCHAR(50) NOT NULL,
  street VARCHAR(100),
  city VARCHAR(100),
  zip CHAR(5),
  email VARCHAR(100)
);
  
CREATE TABLE customer (
  customer_id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL,
  surname VARCHAR(50) NOT NULL,
  street VARCHAR(100) NOT NULL,
  city VARCHAR(100) NOT NULL,
  zip CHAR(5) NOT NULL,
  email VARCHAR(100),
  inactive_since DATE,
  inactive_till DATE, 
  postman_id INTEGER UNSIGNED NOT NULL
);
  
CREATE TABLE publication (
  publication_id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(100) NOT NULL,
  description VARCHAR(200),
  price INTEGER NOT NULL,
  delivery_date DATE NOT NULL,
  next_delivery INTEGER NOT NULL
);

CREATE TABLE invoice (
  invoice_id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  price INTEGER NOT NULL,
  date_created DATE NOT NULL,
  date_due DATE NOT NULL,
  date_paid DATE,
  customer_id INTEGER UNSIGNED NOT NULL
);

CREATE TABLE subscription (
  subscription_id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  publication_id INTEGER UNSIGNED NOT NULL,
  customer_id INTEGER UNSIGNED NOT NULL
);


-- INDICES
ALTER TABLE customer ADD INDEX (postman_id);
ALTER TABLE invoice ADD INDEX (customer_id);
ALTER TABLE subscription ADD INDEX (customer_id);
ALTER TABLE subscription ADD INDEX (publication_id);


-- FOREIGN KEYS
ALTER TABLE customer ADD CONSTRAINT fk_postman_cus FOREIGN KEY (postman_id) REFERENCES postman (postman_id);
ALTER TABLE invoice ADD CONSTRAINT fk_customer_inv FOREIGN KEY (customer_id) REFERENCES customer (customer_id);
ALTER TABLE subscription ADD CONSTRAINT fk_customer_sub FOREIGN KEY (customer_id) REFERENCES customer (customer_id);
ALTER TABLE subscription ADD CONSTRAINT fk_publication_sub FOREIGN KEY (publication_id) REFERENCES publication (publication_id);


-- PROCEDURE

--DROP PROCEDURE delete_postman;

delimiter //

CREATE PROCEDURE delete_postman(del_postman_id INTEGER, new_postman_id INTEGER)
BEGIN
  UPDATE customer SET postman_id = new_postman_id 
  WHERE postman_id = del_postman_id; 
  DELETE FROM postman
  WHERE postman_id = del_postman_id;
END//

delimiter ;


-- TRIGGERS

--DROP TRIGGER deleted_publication;

CREATE TRIGGER deleted_publication BEFORE DELETE ON publication
FOR EACH ROW DELETE FROM subscription
WHERE subscription.publication_id = OLD.publication_id;


-- INSERTS
/**
INSERT INTO admin(login, password, name, surname, email) VALUES ('admin',md5('12345'),'Big','Boss','big@boss.com');				

INSERT INTO postman(login, password, name, surname, street, city, zip, email)
    VALUES ('postman', md5('abcde'), 'František', 'Doručovatel', 'U Mostku 12', 'Brno', '62100', 'frantisek@dorucovatel.cz'); 
INSERT INTO postman(login, password, name, surname, street, city, zip, email)
    VALUES ('bedrich', md5('neheslo'), 'Bedřich', 'Nedoručovatel', 'U sokolovny 42', 'Brno', '63500', 'bedrich@nedorucovatel.cz');
INSERT INTO postman(login, password, name, surname)
    VALUES ('Romča', md5('Heslo'), 'Roman', 'Tyčka'); 

INSERT INTO customer(name, surname, street,
            city, zip, email, postman_id) VALUES ('Prokop', 'Dveře', 'Přístavní 15', 'Brno-Bystrc', '63500', 'prokop@dvere.com', 3);
INSERT INTO customer(name, surname, street,
            city, zip, email, postman_id) VALUES ('Tomáš', 'Jedno', 'Větrná 549', 'Brno-Bystrc', '63500', 'tomas@jedno.com', 3);
INSERT INTO customer(name, surname, street,
            city, zip, postman_id) VALUES ('Petra', 'Kiwiová', 'Odbojářská 69', 'Brno-Bystrc', '63500', 2);
INSERT INTO customer(name, surname, street,
            city, zip, postman_id) VALUES ('Jana', 'Nová', 'Božetěchova 20', 'Brno-Královo Pole', '61200', 1);
INSERT INTO customer(name, surname, street,
            city, zip, postman_id) VALUES ('Zlata', 'Stříbrná', 'Metodějova 42', 'Brno-Královo Pole', '61200', 1);
INSERT INTO customer(name, surname, street,
            city, zip, postman_id) VALUES ('Zlata', 'Stříbrná', 'Chaloupkova 128', 'Brno-Královo Pole', '61200', 1);
INSERT INTO customer(name, surname, street,
            city, zip, postman_id) VALUES ('Michaela', 'Zdravá', 'Slovanské náměstí 256', 'Brno-Královo Pole', '63500', 1);

INSERT INTO publication(title, description, price, delivery_date, next_delivery) VALUES ('Maxim', 'Časopis pro divočáky', 159, CURDATE(), 7);
INSERT INTO publication(title, description, price, delivery_date, next_delivery) VALUES ('Mateřídouška', 'Pro mladší čtenáře.', 99, CURDATE(), 5);
INSERT INTO publication(title, description, price, delivery_date, next_delivery) VALUES ('Bravo', 'Pro princezcny.', 39, CURDATE(), 14);
INSERT INTO publication(title, description, price, delivery_date, next_delivery) VALUES ('MF Dnes', 'Denní tisk.', '399', CURDATE(), 1);
INSERT INTO publication(title, description, price, delivery_date, next_delivery) VALUES ('21. stoleti', '', '139', CURDATE(), 28);
INSERT INTO publication(title, description, price, delivery_date, next_delivery) VALUES ('Sport', '', '299', CURDATE(), 1);
INSERT INTO publication(title, description, price, delivery_date, next_delivery) VALUES ('Křížovky', '', '149', CURDATE(), 2);
INSERT INTO publication(title, description, price, delivery_date, next_delivery) VALUES ('Kulturní novinky', '', '599', CURDATE(), 3);
INSERT INTO publication(title, description, price, delivery_date, next_delivery) VALUES ('Bulvár', '', '9', CURDATE(), 1);

INSERT INTO subscription(publication_id, customer_id) VALUES (1, 1);
INSERT INTO subscription(publication_id, customer_id) VALUES (2, 1);
INSERT INTO subscription(publication_id, customer_id) VALUES (3, 1);
INSERT INTO subscription(publication_id, customer_id) VALUES (4, 1);
INSERT INTO subscription(publication_id, customer_id) VALUES (5, 1);
INSERT INTO subscription(publication_id, customer_id) VALUES (6, 1);
INSERT INTO subscription(publication_id, customer_id) VALUES (7, 1);
INSERT INTO subscription(publication_id, customer_id) VALUES (8, 1);
INSERT INTO subscription(publication_id, customer_id) VALUES (9, 1);
INSERT INTO subscription(publication_id, customer_id) VALUES (3, 2);
INSERT INTO subscription(publication_id, customer_id) VALUES (4, 2);
INSERT INTO subscription(publication_id, customer_id) VALUES (5, 2);
INSERT INTO subscription(publication_id, customer_id) VALUES (6, 2);
INSERT INTO subscription(publication_id, customer_id) VALUES (2, 3);
INSERT INTO subscription(publication_id, customer_id) VALUES (6, 3);
INSERT INTO subscription(publication_id, customer_id) VALUES (9, 3);
INSERT INTO subscription(publication_id, customer_id) VALUES (1, 4);
INSERT INTO subscription(publication_id, customer_id) VALUES (3, 4);
INSERT INTO subscription(publication_id, customer_id) VALUES (4, 4);
INSERT INTO subscription(publication_id, customer_id) VALUES (8, 4);
INSERT INTO subscription(publication_id, customer_id) VALUES (4, 5);
INSERT INTO subscription(publication_id, customer_id) VALUES (5, 5);
INSERT INTO subscription(publication_id, customer_id) VALUES (6, 5);
INSERT INTO subscription(publication_id, customer_id) VALUES (7, 5);
INSERT INTO subscription(publication_id, customer_id) VALUES (9, 5);
INSERT INTO subscription(publication_id, customer_id) VALUES (4, 6);
INSERT INTO subscription(publication_id, customer_id) VALUES (7, 6);
INSERT INTO subscription(publication_id, customer_id) VALUES (1, 7);
INSERT INTO subscription(publication_id, customer_id) VALUES (2, 7);
INSERT INTO subscription(publication_id, customer_id) VALUES (4, 7);
INSERT INTO subscription(publication_id, customer_id) VALUES (6, 7);
INSERT INTO subscription(publication_id, customer_id) VALUES (7, 7);
*/

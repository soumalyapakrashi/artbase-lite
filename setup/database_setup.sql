/* Create the 'artbase' database */
CREATE DATABASE artbase;

/* Select the 'artbase' database for further operations */
USE artbase;

/* Setup the ARTIST table */
CREATE TABLE ARTIST (
	aid VARCHAR(15),
    name VARCHAR(100) NOT NULL,
    birth_place VARCHAR(100) NOT NULL,
    age INT CHECK (age > 0 AND age < 130),
    statement VARCHAR(1000) NOT NULL,
    picture VARCHAR(200),
    INDEX (name),
    PRIMARY KEY (aid)
);

/* Setup the ARTIST_STYLE table */
CREATE TABLE ARTIST_STYLE (
	aid VARCHAR(15) NOT NULL REFERENCES ARTIST(aid),
    style VARCHAR(50) NOT NULL
);

/* Setup ARTIST_SOCIAL table */
CREATE TABLE ARTIST_SOCIAL (
	aid VARCHAR(15) NOT NULL REFERENCES ARTIST(aid),
    social_link VARCHAR(255) NOT NULL
);

/* Setup ARTWORK table */
CREATE TABLE ARTWORK (
	title VARCHAR(255),
    type VARCHAR(100) NOT NULL,
    aid VARCHAR(15) NOT NULL REFERENCES ARTIST(aid),
    year DECIMAL(4),
    size VARCHAR(50) NOT NULL,
    price INT CHECK (price > 0),
    picture VARCHAR(200) NOT NULL,
    status CHAR(4) CHECK (status = 'Sale' OR status = 'View' OR status = 'Sold'),
    PRIMARY KEY (title)
);

/* Setup ARTWORK_KIND table */
CREATE TABLE ARTWORK_KIND (
	title VARCHAR(255) NOT NULL REFERENCES ARTWORK(title),
    kind VARCHAR(100) NOT NULL,
    INDEX (title)
);

/* Setup CUSTOMER table */
CREATE TABLE CUSTOMER (
	cid VARCHAR(15),
    name VARCHAR(100) NOT NULL,
    address VARCHAR(255),
    PRIMARY KEY (cid)
);

/* Setup CUSTOMER_ARTIST table */
CREATE TABLE CUSTOMER_ARTIST (
	cid VARCHAR(15) NOT NULL REFERENCES CUSTOMER(cid),
    aid VARCHAR(15) NOT NULL REFERENCES ARTIST(aid)
);

/* Setup CUSTOMER_KIND table */
CREATE TABLE CUSTOMER_KIND (
	cid VARCHAR(15) NOT NULL REFERENCES CUSTOMER(cid),
    kind VARCHAR(100) NOT NULL
);

/* Setup CUSTOMER_TRANSACTION table */
CREATE TABLE CUSTOMER_TRANSACTION (
	tid VARCHAR(15),
    cid VARCHAR(15) NOT NULL REFERENCES CUSTOMER(cid),
    title VARCHAR(255) NOT NULL UNIQUE REFERENCES ARTWORK(title),
    purchase_price INT NOT NULL,
    PRIMARY KEY (tid)
);

/* Setup ARTIST_TRANSACTION table */
CREATE TABLE ARTIST_TRANSACTION (
	tid VARCHAR(15),
    aid VARCHAR(15) NOT NULL REFERENCES ARTIST(aid),
    title VARCHAR(255) NOT NULL UNIQUE REFERENCES ARTWORK(title),
    PRIMARY KEY (tid)
);

/* Setup ACCOUNT table */
CREATE TABLE ACCOUNT (
	id VARCHAR(15) NOT NULL UNIQUE,
    email VARCHAR(255),
    password VARCHAR(255) NOT NULL,
    PRIMARY KEY (email)
);
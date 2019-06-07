-- Name: LINNI CAI
-- Date: June 1, 2019
-- Section: CSE 154 AO
-- This is the setup.sql file for fooddb.
-- Use it to import to phpMyAdmin,
-- and create tables for myfood, myuser,
-- common, grocery and restaurant.

DROP DATABASE IF EXISTS fooddb;

CREATE DATABASE fooddb;

USE fooddb;

DROP TABLE IF EXISTS MyFood;
DROP TABLE IF EXISTS MyUser;
DROP TABLE IF EXISTS Common;
DROP TABLE IF EXISTS Grocery;
DROP TABLE IF EXISTS Restaurant;

CREATE TABLE MyFood (
	id INT NOT NULL AUTO_INCREMENT,
	name VARCHAR(255) NOT NULL,
	abs VARCHAR(255) NOT NULL,
	cal INT,
	PRIMARY KEY(id)
);

CREATE TABLE MyUser (
	id INT NOT NULL AUTO_INCREMENT,
	name VARCHAR(255) NOT NULL,
	pass VARCHAR(255) NOT NULL,
	PRIMARY KEY(id)
);

CREATE TABLE Common (
  id INT NOT NULL AUTO_INCREMENT,
  brand_name VARCHAR(255) DEFAULT NULL,
  item_name VARCHAR(255) DEFAULT NULL,
  brand_id VARCHAR(255) DEFAULT NULL,
  item_id VARCHAR(255) DEFAULT NULL,
  upc VARCHAR(4) DEFAULT NULL,
  item_type INT(1) DEFAULT NULL,
  item_description VARCHAR(255) DEFAULT NULL,
  nf_ingredient_statement VARCHAR(255) DEFAULT NULL,
  nf_calories DECIMAL(10,2) DEFAULT NULL,
  nf_calories_from_fat DECIMAL(10,2) DEFAULT NULL,
  nf_total_fat DECIMAL(10,2) DEFAULT NULL,
  nf_saturated_fat DECIMAL(10,2) DEFAULT NULL,
  nf_trans_fatty_acid DECIMAL(10,2) DEFAULT NULL,
  nf_polyunsaturated_fat DECIMAL(10,2) DEFAULT NULL,
  nf_monounsaturated_fat DECIMAL(10,2) DEFAULT NULL,
  nf_cholesterol DECIMAL(10,2) DEFAULT NULL,
  nf_sodium DECIMAL(10,2) DEFAULT NULL,
  nf_total_carbohydrate DECIMAL(10,2) DEFAULT NULL,
  nf_dietary_fiber DECIMAL(10,2) DEFAULT NULL,
  nf_sugars DECIMAL(10,2) DEFAULT NULL,
  nf_protein DECIMAL(10,2) DEFAULT NULL,
  nf_vitamin_a_dv DECIMAL(10,2) DEFAULT NULL,
  nf_vitamin_c_dv DECIMAL(10,2) DEFAULT NULL,
  nf_calcium_dv DECIMAL(10,2) DEFAULT NULL,
  nf_iron_dv DECIMAL(10,2) DEFAULT NULL,
  nf_servings_per_container DECIMAL(10,2) DEFAULT NULL,
  nf_serving_size_qty DECIMAL(10,2) DEFAULT NULL,
  nf_serving_size_unit VARCHAR(255) DEFAULT NULL,
  nf_serving_weight_grams DECIMAL(10,2) DEFAULT NULL,
  updated_at VARCHAR(25) DEFAULT NULL,
  PRIMARY KEY(id)
);

CREATE TABLE Grocery (
  id INT NOT NULL AUTO_INCREMENT,
  brand_name VARCHAR(255) DEFAULT NULL,
  item_name VARCHAR(255) DEFAULT NULL,
  brand_id VARCHAR(255) DEFAULT NULL,
  item_id VARCHAR(255) DEFAULT NULL,
  upc VARCHAR(255) DEFAULT NULL,
  item_type INT(1) DEFAULT NULL,
  item_description VARCHAR(255) DEFAULT NULL,
  nf_ingredient_statement VARCHAR(2000) DEFAULT NULL,
  nf_calories DECIMAL(10,2) DEFAULT NULL,
  nf_calories_from_fat DECIMAL(10,2) DEFAULT NULL,
  nf_total_fat DECIMAL(10,2) DEFAULT NULL,
  nf_saturated_fat DECIMAL(10,2) DEFAULT NULL,
  nf_trans_fatty_acid DECIMAL(10,2) DEFAULT NULL,
  nf_polyunsaturated_fat DECIMAL(10,2) DEFAULT NULL,
  nf_monounsaturated_fat DECIMAL(10,2) DEFAULT NULL,
  nf_cholesterol DECIMAL(10,2) DEFAULT NULL,
  nf_sodium DECIMAL(10,2) DEFAULT NULL,
  nf_total_carbohydrate DECIMAL(10,2) DEFAULT NULL,
  nf_dietary_fiber DECIMAL(10,2) DEFAULT NULL,
  nf_sugars DECIMAL(10,2) DEFAULT NULL,
  nf_protein DECIMAL(10,2) DEFAULT NULL,
  nf_vitamin_a_dv DECIMAL(10,2) DEFAULT NULL,
  nf_vitamin_c_dv DECIMAL(10,2) DEFAULT NULL,
  nf_calcium_dv DECIMAL(10,2) DEFAULT NULL,
  nf_iron_dv DECIMAL(10,2) DEFAULT NULL,
  nf_potassium DECIMAL(10,2) DEFAULT NULL,
  nf_servings_per_container DECIMAL(10,2) DEFAULT NULL,
  nf_serving_size_qty DECIMAL(10,2) DEFAULT NULL,
  nf_serving_size_unit VARCHAR(255) DEFAULT NULL,
  nf_serving_weight_grams DECIMAL(10,2) DEFAULT NULL,
  metric_qty DECIMAL(10,2) DEFAULT NULL,
  metric_uom VARCHAR(255) DEFAULT NULL,
  images_front_full_url VARCHAR(255) DEFAULT NULL,
  updated_at VARCHAR(255) DEFAULT NULL,
  section_ids VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY(id)
);

CREATE TABLE Restaurant (
  id INT NOT NULL AUTO_INCREMENT,
  brand_name VARCHAR(255) DEFAULT NULL,
  item_name VARCHAR(255) DEFAULT NULL,
  brand_id VARCHAR(255) DEFAULT NULL,
  item_id VARCHAR(255) DEFAULT NULL,
  upc VARCHAR(255) DEFAULT NULL,
  item_type INT(1) DEFAULT NULL,
  item_description VARCHAR(255) DEFAULT NULL,
  nf_ingredient_statement VARCHAR(255) DEFAULT NULL,
  nf_calories DECIMAL(10,2) DEFAULT NULL,
  nf_calories_from_fat DECIMAL(10,2) DEFAULT NULL,
  nf_total_fat DECIMAL(10,2) DEFAULT NULL,
  nf_saturated_fat DECIMAL(10,2) DEFAULT NULL,
  nf_trans_fatty_acid DECIMAL(10,2) DEFAULT NULL,
  nf_polyunsaturated_fat DECIMAL(10,2) DEFAULT NULL,
  nf_monounsaturated_fat DECIMAL(10,2) DEFAULT NULL,
  nf_cholesterol DECIMAL(10,2) DEFAULT NULL,
  nf_sodium DECIMAL(10,2) DEFAULT NULL,
  nf_total_carbohydrate DECIMAL(10,2) DEFAULT NULL,
  nf_dietary_fiber DECIMAL(10,2) DEFAULT NULL,
  nf_sugars DECIMAL(10,2) DEFAULT NULL,
  nf_protein DECIMAL(10,2) DEFAULT NULL,
  nf_vitamin_a_dv DECIMAL(10,2) DEFAULT NULL,
  nf_vitamin_c_dv DECIMAL(10,2) DEFAULT NULL,
  nf_calcium_dv DECIMAL(10,2) DEFAULT NULL,
  nf_iron_dv DECIMAL(10,2) DEFAULT NULL,
  nf_potassium DECIMAL(10,2) DEFAULT NULL,
  nf_servings_per_container DECIMAL(10,2) DEFAULT NULL,
  nf_serving_size_qty DECIMAL(10,2) DEFAULT NULL,
  nf_serving_size_unit VARCHAR(255) DEFAULT NULL,
  nf_serving_weight_grams DECIMAL(10,2) DEFAULT NULL,
  images_front_full_url VARCHAR(255) DEFAULT NULL,
  updated_at VARCHAR(255) DEFAULT NULL,
  section_id VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY(id)
);
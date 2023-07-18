

CREATE TABLE IF NOT EXISTS `pedigree` (
  `pedigree_id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `pedigree_publish` tinyint(1) NOT NULL DEFAULT '1',
  `created_on` datetime NOT NULL,
  `last_updated` datetime NOT NULL,
  `accounts_id` int NOT NULL,
  `user_id` int NOT NULL,
  `pedigree_name` varchar(100) COLLATE utf8mb3_unicode_ci NOT NULL,
  `tag` varchar(50) COLLATE utf8mb3_unicode_ci NOT NULL,
  `breed_id` int NOT NULL,
  `gender_id` int NOT NULL,
  `birth_date` date NOT NULL,
  `last_weight` varchar(25) COLLATE utf8mb3_unicode_ci NOT NULL,
  `last_height` varchar(50) COLLATE utf8mb3_unicode_ci NOT NULL,
  `colour_name` varchar(15) COLLATE utf8mb3_unicode_ci NOT NULL,
  `physical_condition` varchar(3) COLLATE utf8mb3_unicode_ci NOT NULL,  
  `sku` varchar(20) COLLATE utf8mb3_unicode_ci NOT NULL,
  `anml_history` text COLLATE utf8mb3_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb3_unicode_ci NOT NULL,
  `age_in_year` float NOT NULL,
  `no_of_teeth` int NOT NULL,
  `current_address` text COLLATE utf8mb3_unicode_ci NOT NULL 'current owner details',
  `calving_no` int NOT NULL,
  `calving_count` int NOT NULL,
  `birth_location` text COLLATE utf8mb3_unicode_ci NOT NULL,
  
  `suppliers_id` int NOT NULL,
  
  PRIMARY KEY (`pedigree_id`),
  KEY `created_by` (`accounts_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
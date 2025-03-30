- Download the repository to your server

- Set the root folder in the virtualhost as the root folder of the repository

- Install phpmailer using composer require phpmailer/phpmailer

- Setup the database:

	CREATE DATABASE `wallet_manager`;
	CREATE TABLE `auth_tokens` (
	  `id` int NOT NULL AUTO_INCREMENT,
	  `selector` varchar(16) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
	  `token` varchar(64) NOT NULL,
	  `user_id` int NOT NULL,
	  `username` varchar(50) NOT NULL,
	  `expires` datetime NOT NULL,
	  PRIMARY KEY (`id`),
	  UNIQUE KEY `selector` (`selector`)
	) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb3;

	CREATE TABLE `users` (
	  `id` int NOT NULL AUTO_INCREMENT,
	  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
	  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
	  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
	  `surname` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
	  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
	  `verified` tinyint(1) NOT NULL DEFAULT '0',
	  `salary_date` smallint DEFAULT NULL,
	  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
	  PRIMARY KEY (`id`),
	  UNIQUE KEY `email` (`email`),
	  UNIQUE KEY `username` (`username`),
	  UNIQUE KEY `unique_email` (`email`)
	) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

	CREATE TABLE `wallet_data` (
	  `id` int NOT NULL AUTO_INCREMENT,
	  `user_id` int NOT NULL,
	  `wallet_id` int NOT NULL,
	  `description` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
	  `amount` decimal(10,2) NOT NULL,
	  `buying_date` date NOT NULL,
	  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
	  PRIMARY KEY (`id`),
	  KEY `fk_user_id_revision` (`user_id`) USING BTREE
	) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

	CREATE TABLE `wallet_data_parts` (
	  `id` int NOT NULL AUTO_INCREMENT,
	  `user_id` int NOT NULL,
	  `wallet_data_id` int NOT NULL,
	  `part_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
	  `part_cost` decimal(10,2) NOT NULL,
	  PRIMARY KEY (`id`),
	  KEY `fk_vehicles_id_service_parts` (`wallet_data_id`) USING BTREE,
	  KEY `user_id` (`user_id`) USING BTREE
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

	CREATE TABLE `wallets` (
	  `id` int NOT NULL AUTO_INCREMENT,
	  `user_id` int NOT NULL,
	  `description` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
	  `icon` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
	  `show_in_dashboard` tinyint(1) NOT NULL,
	  `shared_with` json DEFAULT NULL,
	  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
	  `deleted_at` timestamp NULL DEFAULT NULL,
	  PRIMARY KEY (`id`),
	  KEY `user_id` (`user_id`) USING BTREE
	) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

	CREATE TABLE `email_verifications` (
	  `user_id` int NOT NULL,
	  `email` varchar(255) NOT NULL,
	  `token` varchar(100) NOT NULL,
	  `expires` int NOT NULL,
	  PRIMARY KEY (`user_id`),
	  CONSTRAINT `email_verifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

	CREATE TABLE `password_resets` (
	  `id` int NOT NULL AUTO_INCREMENT,
	  `user_id` int NOT NULL,
	  `email` varchar(255) NOT NULL,
	  `token` varchar(255) NOT NULL,
	  `expires` int NOT NULL,
	  PRIMARY KEY (`id`),
	  KEY `user_id` (`user_id`),
	  CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
	) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

	CREATE TABLE `vehicle_service_parts` (
	  `id` int NOT NULL AUTO_INCREMENT,
	  `user_id` int NOT NULL,
	  `service_id` int NOT NULL,
	  `part_name` varchar(100) NOT NULL,
	  `part_number` varchar(100) NOT NULL,
	  PRIMARY KEY (`id`),
	  KEY `fk_vehicles_id_service_parts` (`service_id`),
	  KEY `user_id` (`user_id`),
	  CONSTRAINT `vehicle_service_parts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
	  CONSTRAINT `vehicle_service_parts_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
	) ENGINE=InnoDB AUTO_INCREMENT=128 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

	CREATE TABLE `vehicles` (
	  `id` int NOT NULL AUTO_INCREMENT,
	  `user_id` int NOT NULL,
	  `description` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
	  `buying_date` date NOT NULL,
	  `registration_date` date NOT NULL,
	  `plate_number` varchar(7) NOT NULL,
	  `chassis_number` varchar(100) DEFAULT NULL,
	  `tax_month` smallint NOT NULL,
	  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
	  `deleted_at` timestamp NULL DEFAULT NULL,
	  PRIMARY KEY (`id`),
	  KEY `user_id` (`user_id`) USING BTREE,
	  CONSTRAINT `vheicle_users_FK` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
	) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

	CREATE TABLE `wallet_estimated_expenses` (
	  `id` int NOT NULL AUTO_INCREMENT,
	  `user_id` int NOT NULL,
	  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
	  `amount` decimal(10,2) NOT NULL,
	  `start_month` int NOT NULL,
	  `start_year` int NOT NULL,
	  `end_month` int DEFAULT NULL,
	  `end_year` int DEFAULT NULL,
	  `undetermined` tinyint(1) DEFAULT '0',
	  `debit_date` tinyint NOT NULL,
	  `billing_frequency` int NOT NULL,
	  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
	  PRIMARY KEY (`id`),
	  KEY `user_id` (`user_id`) USING BTREE,
	  CONSTRAINT `estimated_expenses_users_FK` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
	  CONSTRAINT `wallet_estimated_expenses_chk_1` CHECK ((`start_year` between 2000 and 2999)),
	  CONSTRAINT `wallet_estimated_expenses_chk_2` CHECK ((`start_month` between 1 and 12)),
	  CONSTRAINT `wallet_estimated_expenses_chk_3` CHECK ((`end_year` between 2000 and 2999)),
	  CONSTRAINT `wallet_estimated_expenses_chk_4` CHECK ((`end_month` between 1 and 12))
	) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

	CREATE TABLE `wallet_extra_expenses` (
	  `id` int NOT NULL AUTO_INCREMENT,
	  `user_id` int NOT NULL,
	  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
	  `amount` decimal(10,2) NOT NULL,
	  `debit_date` date NOT NULL,
	  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
	  PRIMARY KEY (`id`),
	  KEY `user_id` (`user_id`) USING BTREE,
	  CONSTRAINT `extra_expenses_users_FK` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
	) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

	CREATE TABLE `wallet_incomes` (
	  `id` int NOT NULL AUTO_INCREMENT,
	  `user_id` int NOT NULL,
	  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
	  `amount` decimal(10,2) NOT NULL,
	  `added_date` date NOT NULL,
	  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
	  PRIMARY KEY (`id`),
	  KEY `user_id` (`user_id`) USING BTREE,
	  CONSTRAINT `salaries_users_FK` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
	) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

	CREATE TABLE `wallet_piggy_bank` (
	  `id` int NOT NULL AUTO_INCREMENT,
	  `user_id` int NOT NULL,
	  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
	  `amount` decimal(10,2) NOT NULL,
	  `added_date` date NOT NULL,
	  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
	  PRIMARY KEY (`id`),
	  KEY `user_id` (`user_id`) USING BTREE,
	  CONSTRAINT `piggy_bank_users_FK` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
	) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

	CREATE TABLE `wallet_recurring_expenses` (
	  `id` int NOT NULL AUTO_INCREMENT,
	  `user_id` int NOT NULL,
	  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
	  `amount` decimal(10,2) NOT NULL,
	  `start_month` int DEFAULT NULL,
	  `start_year` int DEFAULT NULL,
	  `end_month` int DEFAULT NULL,
	  `end_year` int DEFAULT NULL,
	  `undetermined` tinyint(1) DEFAULT '0',
	  `debit_date` tinyint NOT NULL,
	  `billing_frequency` int NOT NULL,
	  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
	  PRIMARY KEY (`id`),
	  KEY `user_id` (`user_id`) USING BTREE,
	  CONSTRAINT `recurring_expenses_users_FK` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
	  CONSTRAINT `wallet_recurring_expenses_chk_1` CHECK ((`start_year` between 2000 and 2999)),
	  CONSTRAINT `wallet_recurring_expenses_chk_2` CHECK ((`start_month` between 1 and 12)),
	  CONSTRAINT `wallet_recurring_expenses_chk_3` CHECK ((`end_year` between 2000 and 2999)),
	  CONSTRAINT `wallet_recurring_expenses_chk_4` CHECK ((`end_month` between 1 and 12))
	) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

	CREATE TABLE `vehicle_insurances` (
	  `id` int NOT NULL AUTO_INCREMENT,
	  `user_id` int NOT NULL,
	  `vehicle_id` int NOT NULL,
	  `company` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
	  `amount` decimal(10,2) NOT NULL,
	  `buying_date` date NOT NULL,
	  `effective_date` date NOT NULL,
	  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
	  PRIMARY KEY (`id`),
	  KEY `fk_user_id` (`user_id`),
	  KEY `fk_vehicle_id` (`vehicle_id`),
	  CONSTRAINT `fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
	  CONSTRAINT `fk_vehicle_id` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`)
	) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

	CREATE TABLE `vehicle_revisions` (
	  `id` int NOT NULL AUTO_INCREMENT,
	  `user_id` int NOT NULL,
	  `vehicle_id` int NOT NULL,
	  `amount` decimal(10,2) NOT NULL,
	  `buying_date` date NOT NULL,
	  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
	  PRIMARY KEY (`id`),
	  KEY `fk_vehicle_id_revision` (`vehicle_id`),
	  KEY `fk_user_id_revision` (`user_id`),
	  CONSTRAINT `fk_user_id_revision` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
	  CONSTRAINT `fk_vehicle_id_revision` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`)
	) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

	CREATE TABLE `vehicle_services` (
	  `id` int NOT NULL AUTO_INCREMENT,
	  `user_id` int NOT NULL,
	  `vehicle_id` int NOT NULL,
	  `description` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
	  `amount` decimal(10,2) NOT NULL,
	  `buying_date` date NOT NULL,
	  `registered_kilometers` int DEFAULT NULL,
	  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
	  `attachment_path` varchar(255) DEFAULT NULL,
	  PRIMARY KEY (`id`),
	  KEY `fk_user_id_revision` (`user_id`) USING BTREE,
	  KEY `fk_vehicle_id_revision` (`vehicle_id`) USING BTREE,
	  CONSTRAINT `fk_user_id_revision_copy` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
	  CONSTRAINT `fk_vehicle_id_revision_copy` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
	) ENGINE=InnoDB AUTO_INCREMENT=84 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

	CREATE TABLE `vehicle_taxes` (
	  `id` int NOT NULL AUTO_INCREMENT,
	  `user_id` int NOT NULL,
	  `vehicle_id` int NOT NULL,
	  `amount` decimal(10,2) NOT NULL,
	  `buying_date` date NOT NULL,
	  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
	  PRIMARY KEY (`id`),
	  KEY `fk_user_id_taxes` (`user_id`),
	  KEY `fk_vehicles_id_taxes` (`vehicle_id`),
	  CONSTRAINT `fk_user_id_taxes` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
	  CONSTRAINT `fk_vehicles_id_taxes` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`)
	) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

- Copy the config.php.default to config.php and edit it according to your needs. DO NOT forget the HOST_BASE (your URI) and the 
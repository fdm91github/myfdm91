- Download the repository to your server

- Set the root folder in the virtualhost as the root folder of the repository

- Create the database:

	CREATE DATABASE `wallet_manager` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
	CREATE TABLE `auth_tokens` (
	  `id` int NOT NULL AUTO_INCREMENT,
	  `selector` varchar(16) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
	  `token` varchar(64) NOT NULL,
	  `user_id` int NOT NULL,
	  `username` varchar(50) NOT NULL,
	  `expires` datetime NOT NULL,
	  PRIMARY KEY (`id`),
	  UNIQUE KEY `selector` (`selector`)
	) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;
	CREATE TABLE `users` (
	  `id` int NOT NULL AUTO_INCREMENT,
	  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
	  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
	  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
	  `surname` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
	  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
	  `salary_date` smallint DEFAULT NULL,
	  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
	  PRIMARY KEY (`id`),
	  UNIQUE KEY `email` (`email`),
	  UNIQUE KEY `username` (`username`)
	) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
	CREATE TABLE `estimated_expenses` (
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
	  CONSTRAINT `estimated_expenses_chk_1` CHECK ((`start_year` between 2000 and 2999)),
	  CONSTRAINT `estimated_expenses_chk_2` CHECK ((`start_month` between 1 and 12)),
	  CONSTRAINT `estimated_expenses_chk_3` CHECK ((`end_year` between 2000 and 2999)),
	  CONSTRAINT `estimated_expenses_chk_4` CHECK ((`end_month` between 1 and 12))
	) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
	CREATE TABLE `extra_expenses` (
	  `id` int NOT NULL AUTO_INCREMENT,
	  `user_id` int NOT NULL,
	  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
	  `amount` decimal(10,2) NOT NULL,
	  `debit_date` date NOT NULL,
	  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
	  PRIMARY KEY (`id`),
	  KEY `user_id` (`user_id`) USING BTREE,
	  CONSTRAINT `extra_expenses_users_FK` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
	) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
	CREATE TABLE `incomes` (
	  `id` int NOT NULL AUTO_INCREMENT,
	  `user_id` int NOT NULL,
	  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
	  `amount` decimal(10,2) NOT NULL,
	  `added_date` date NOT NULL,
	  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
	  PRIMARY KEY (`id`),
	  KEY `user_id` (`user_id`) USING BTREE,
	  CONSTRAINT `salaries_users_FK` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
	) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
	CREATE TABLE `piggy_bank` (
	  `id` int NOT NULL AUTO_INCREMENT,
	  `user_id` int NOT NULL,
	  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
	  `amount` decimal(10,2) NOT NULL,
	  `added_date` date NOT NULL,
	  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
	  PRIMARY KEY (`id`),
	  KEY `user_id` (`user_id`) USING BTREE,
	  CONSTRAINT `piggy_bank_users_FK` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
	) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
	CREATE TABLE `recurring_expenses` (
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
	  CONSTRAINT `recurring_expenses_chk_1` CHECK ((`start_year` between 2000 and 2999)),
	  CONSTRAINT `recurring_expenses_chk_2` CHECK ((`start_month` between 1 and 12)),
	  CONSTRAINT `recurring_expenses_chk_3` CHECK ((`end_year` between 2000 and 2999)),
	  CONSTRAINT `recurring_expenses_chk_4` CHECK ((`end_month` between 1 and 12))
	) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

- Copy the config.php.default to config.php and edit it according to your needs
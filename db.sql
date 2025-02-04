-- データベースの削除
DROP DATABASE `memolist`;

-- データベースの作成
CREATE DATABASE `memolist`;

-- データベースの選択
USE `memolist`;

-- usersテーブルの作成
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(12) NOT NULL,
  `password` text NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
);

-- memosテーブルの作成
CREATE TABLE `memos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `position` int(11) NOT NULL,
  `memo` varchar(100) NOT NULL,
  `bgcolor` varchar(50) NOT NULL,
  `userid` int(11) NOT NULL,
  `created` datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
  PRIMARY KEY (`id`)
);
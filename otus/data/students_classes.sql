CREATE TABLE `students` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `fname` VARCHAR(45) NOT NULL,
  `lname` VARCHAR(45) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `user_data` (`fname` ASC, `lname` ASC, `email` ASC));

  CREATE TABLE `classes` (
  `class_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `class_name` VARCHAR(200) NULL,
  PRIMARY KEY (`class_id`),
  INDEX `class_data` (`class_name` ASC));

  CREATE TABLE `student_classes` (
  `scid` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` INT NULL,
  `student_class` INT(11) NULL,
  `student_grade` DECIMAL(1,2) NULL,
  PRIMARY KEY (`scid`),
  INDEX `sc_data` (`student_id` ASC, `student_class` ASC));
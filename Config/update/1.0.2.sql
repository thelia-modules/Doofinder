SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `doofinder_dfscore_product`;

CREATE TABLE `doofinder_dfscore_product`
(
    `product_id` INTEGER NOT NULL,
    `dfscore` FLOAT DEFAULT 1.0,
    `created_at` DATETIME,
    `updated_at` DATETIME,
    PRIMARY KEY (`product_id`),
    CONSTRAINT `fk_doofinder_dfscore_product_product_id`
        FOREIGN KEY (`product_id`)
            REFERENCES `product` (`id`)
            ON UPDATE RESTRICT
            ON DELETE CASCADE
) ENGINE=InnoDB;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;

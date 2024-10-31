
-- Section1

UPDATE `users` SET `is_suspend` = 1 WHERE `reported_num` >= 5;

-- Section2

SELECT `users`.`name` as `blocked_user_name`, COUNT(`block_list`.`id`) as `block_count`
FROM `users`
         LEFT JOIN `block_list` ON `block_list`.`blocked_user_id` = `users`.`id`
GROUP BY `users`.`id`
ORDER BY `block_count` DESC, `blocked_user_name`;

-- Section3

SELECT
    `a`.`name` as `first_user`,
    `b`.`name` as `second_user`,
    `group`.`name` as `mutual_group`
FROM `users` `a`
         INNER JOIN `group_conversation_users` `g` ON `g`.`user_id` = `a`.`id`
         INNER JOIN `group_conversations` `group` ON `group`.`id` = `g`.`group_id`
         INNER JOIN `group_conversation_users` `g2` ON `g2`.`group_id` = `g`.`group_id` AND `g2`.`user_id` < `a`.`id`
         INNER JOIN `users` `b` ON `b`.`id` = `g2`.`user_id`
ORDER BY `first_user`, `second_user`, `mutual_group`;

-- Section4

delimiter $$
CREATE TRIGGER `update_suspend` BEFORE UPDATE ON `users`
    FOR EACH ROW
    BEGIN IF NEW.reported_num >= 5 THEN
        SET NEW.is_suspend = 1;
    ELSE
        SET NEW.is_suspend = 0;
    END IF;
END;
$$ delimiter;
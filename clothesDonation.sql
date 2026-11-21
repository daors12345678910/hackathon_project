

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";



CREATE TABLE `charity_profiles` (
  `id` int(255) NOT NULL,
  `charity_name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `city` int(255) NOT NULL,
  `symptom_image` varchar(255) NOT NULL,
  `user` varchar(255) NOT NULL,
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


INSERT INTO `symptoms` (`id`, `symptom_name`, `symptom_diagnosis`, `symptom_treatment`, `symptom_image`) VALUES
(1, 'headache', 'Tension-type headache.', 'Rest in a quiet environment. Drink enough water. Take breaks from screens. Try relaxing your neck and shoulder muscles. Seek medical advice if headaches keep returning or worsen.', ''),
(2, 'cough', 'Common cold. ',' Drink plenty of fluids. Rest.Use warm drinks to soothe your throat. Avoid cigarette smoke and other irritants. Seek medical advice if the cough persists or gets worse.', ''),
(3, 'stomach pain', 'Indigestion.', 'Eat smaller meals. Avoid foods that seem to trigger discomfort. Drink water. Avoid lying down immediately after eating. Seek medical advice if the pain continues or returns frequently.',   '');




CREATE TABLE `users` (
  `id` int(255) NOT NULL,
  `emri` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `confirm_password` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


INSERT INTO `users` (`id`, `emri`, `username`, `email`, `password`, `confirm_password`, `is_admin`) VALUES
(1, 'test', 'test', 'test@gmail.com', '$2y$10$0VBVvKNyxIl9OA00/FRmrep7atIIE/SoLXGEoXcjNmF.4ZV6b3NIG', '$2y$10$0VBVvKNyxIl9OA00/FRmrep7atIIE/SoLXGEoXcjNmF.4ZV6b3NIG', 'true'),
(2, 'test1', 'test1', 'test1@test1.com', '$2y$10$erk7uFn0RpqhC6g3VX.XcuyU3CVkOanelghahzqjmdtxeFwfEgQQO', '$2y$10$2XuH6X04NLc/jwMhciX8OOccIfFHom7oi6o2PSh82Z8BnINw.nu2O', ''),
(3, 'test2', 'test2', 'linda@linda.com', '$2y$10$pzATPYrl/LDDmFp8DIxalOrvUNT2RL4.I3pFbRjGrsUE8L/xH9O32', '$2y$10$q.HH1oolBta2lTtF/uCIGu2lwLhoHvKoWljOfqV1TAB5ER44EAQUG', 'true');


ALTER TABLE `symptoms`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `symptoms`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;


ALTER TABLE `users`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

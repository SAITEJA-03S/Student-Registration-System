-- Initial Seeding for Student Registration System

USE `student_reg_db`;

-- Default Admin User (admin / admin123)
INSERT INTO `users` (`username`, `password`, `role`) VALUES
('admin', '$2y$10$eO0W7/1iU7P58/4r5bV7sOXfW6Ld9o8A2D7P/k6y/7F7/Q0Q9w1rK', 'admin')
ON DUPLICATE KEY UPDATE `username`=`username`;

-- Sample Student Records matching the Phase 1 & 2 documentation
INSERT INTO `students` (`fullname`, `email`, `mobile`, `dob`, `gender`, `course`, `semester`, `hobbies`, `address`, `photo`, `password`) VALUES
('Rahul Kumar', 'rahul@example.com', '9876543210', '2003-05-22', 'Male', 'BCA', 'Semester 5', 'Reading, Sports', '123, New Colony, Bhopal, Madhya Pradesh', 'default_avatar.png', '$2y$10$eO0W7/1iU7P58/4r5bV7sOXfW6Ld9o8A2D7P/k6y/7F7/Q0Q9w1rK'),
('Priya Sharma', 'priya@example.com', '9822334455', '2002-11-14', 'Female', 'BBA', 'Semester 3', 'Music, Reading', '45 Park Avenue, Indore, MP', 'default_avatar.png', '$2y$10$eO0W7/1iU7P58/4r5bV7sOXfW6Ld9o8A2D7P/k6y/7F7/Q0Q9w1rK'),
('Aman Verma', 'aman@example.com', '9687452012', '2001-08-19', 'Male', 'MCA', 'Semester 2', 'Sports, Coding', '78 Sector 9, Gwalior, MP', 'default_avatar.png', '$2y$10$eO0W7/1iU7P58/4r5bV7sOXfW6Ld9o8A2D7P/k6y/7F7/Q0Q9w1rK'),
('Neha Singh', 'neha@example.com', '9712345678', '2003-02-10', 'Female', 'BCA', 'Semester 4', 'Music, Other', '89 Lake View Road, Jabalpur, MP', 'default_avatar.png', '$2y$10$eO0W7/1iU7P58/4r5bV7sOXfW6Ld9o8A2D7P/k6y/7F7/Q0Q9w1rK'),
('Vikram Patel', 'vikram@example.com', '9898989898', '2002-04-15', 'Male', 'B.Tech', 'Semester 6', 'Reading, Gaming', '12 Tech Hub, Bhopal, MP', 'default_avatar.png', '$2y$10$eO0W7/1iU7P58/4r5bV7sOXfW6Ld9o8A2D7P/k6y/7F7/Q0Q9w1rK'),
('Ananya Roy', 'ananya@example.com', '9777888999', '2004-09-25', 'Female', 'BBA', 'Semester 1', 'Reading, Music', '55 Green Meadows, Ujjain, MP', 'default_avatar.png', '$2y$10$eO0W7/1iU7P58/4r5bV7sOXfW6Ld9o8A2D7P/k6y/7F7/Q0Q9w1rK');


-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 20, 2024 at 10:10 AM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `blogdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `post_id`, `user_id`, `content`, `created_at`) VALUES
(45, 17, 5, 'Hello!', '2024-12-20 03:57:56'),
(49, 16, 9, 'Hi!', '2024-12-20 06:17:39'),
(53, 21, 5, 'POGI!', '2024-12-20 07:01:43'),
(55, 21, 9, 'SSOB!', '2024-12-20 07:02:16');

-- --------------------------------------------------------

--
-- Table structure for table `likes`
--

CREATE TABLE `likes` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `likes`
--

INSERT INTO `likes` (`id`, `post_id`, `user_id`, `created_at`) VALUES
(33, 21, 5, '2024-12-20 15:24:17'),
(34, 17, 5, '2024-12-20 15:24:19'),
(35, 16, 5, '2024-12-20 15:24:20'),
(37, 15, 5, '2024-12-20 15:24:32'),
(43, 21, 9, '2024-12-20 15:27:07'),
(44, 17, 9, '2024-12-20 15:27:08'),
(45, 16, 9, '2024-12-20 15:27:09'),
(46, 15, 9, '2024-12-20 15:27:09'),
(60, 15, 11, '2024-12-20 15:34:16');

-- --------------------------------------------------------

--
-- Table structure for table `postimages`
--

CREATE TABLE `postimages` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `postimages`
--

INSERT INTO `postimages` (`id`, `post_id`, `image_path`, `created_at`) VALUES
(9, 15, 'uploads/drone-shot-where-you.jpg', '2024-12-20 01:35:16'),
(10, 16, 'uploads/Seda_Lio_El_Nido_Reviewed-1-1440x1080.jpg', '2024-12-20 01:36:02'),
(11, 17, 'uploads/Bohol.jpg', '2024-12-20 01:42:51'),
(15, 21, 'uploads/465768110_1266654457933426_6760798930402049303_n.jpg', '2024-12-20 06:59:56');

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `category` enum('optimization','troubleshooting') NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `likes_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `category_id` int(11) DEFAULT NULL,
  `related_to` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `title`, `content`, `category`, `image_path`, `likes_count`, `created_at`, `category_id`, `related_to`) VALUES
(15, 'Guide and Review: Perth Paradise and Resort', 'Budget Friendly: Affordable High-End Experience\r\nA Brief Overview of Perth Paradise Resort\r\nDip into an infinity pool with a stunning view of the islets surrounding the resort is what you can expect at Perth Paradise Resort in Sipalay, Negros Occidental. If you want to avoid the tourist-magnet Bacolod or Dumaguete, a promising and relaxing experience awaits for you with just 4-5 hours drive. Sipalay in Negros Occidental boasts off a number of budget resorts that is perfect for family gathering and barkada outings. Even for solo travelers like me, the accommodations provided are cheap and very affordable. The best one of them all and I highly recommend most especially for budget travelers is the Perth Paradise Resort.\r\nWhere to Sipalay, Negros Occidental\r\nIf you have been browsing on social media for quite some time now, you probably already know that Perth Paradise Resort is a popular destination in Western Visayas. Aside from its tranquil ambiance and a close to nature vibe, tourists are also coming here because it fits right on the budget.\r\nHow To Get There\r\nFrom Bacolod\r\nSipalay is located in the Southern part of Negros Occidental which is also a 4-5 hour drive from Bacolod City. Ride a Ceres Bus in Bacolod City bound for Sipalay. The fare is PHP280.00, with a 20% discount for students, seniors, & PWD and the travel time is 5-6 hours. There’s also an option to ride a van (DPL Van Terminal, in front of Ceres Bus Terminal) from Bacolod to Sipalay and the fare is PHP250.00 and travel time is only 3-4 hours. From the terminal, charter a tricycle going to Perth Paradise Resort, the fare is PHP70.00 per person.\r\n\r\nFrom Iloilo - Bacolod\r\nFerries from Iloilo - Bacolod from Php 593, with discount tickets for students, seniors, & PWD.\r\nThings To Do\r\nSwimming Infinity Pool\r\nExperience swimming in the infinity pool of the resort that has a stunning view of the islets is probably the highlight of your Sipalay trip. Don’t miss getting that Instagram-worthy photos for your feed! I honestly enjoyed swimming here even though the pool is just small because the view is really beautiful! What more can I ask for? A good swim with a paradise view is enough to satisfy a relaxing vacation.\r\nIsland Hopping\r\nIsland hopping is famous in Sipalay and the boat rental is only P1500.00 (USD29) goof for maximum of 6 people. Islands to visit includes Tinagong Dagat, Punta Ballo, Island Cave, and Perth Paradise Resort. You can also do land tour with tricycle drivers and the rate is P500.00 (USD10) per person. The prominent list of sights includes Poblacion Beach, Wow Sipalay Signage, Campoquino Bay, and Punta Ballo.\r\n\r\nExperience Nature at the Floating Cottage\r\nUpon entering the resort, you’ll first see the floating cottage just beside the restaurant. It’s quite big and there are locals and tourists swimming by the floating cottage. This is another picturesque spot and it’s a close to nature experience. You’ll only see trees and island beside the floating cottage while swimming or just enjoying the view.\r\nKayaking, Snorkeling and Scuba Diving\r\nThese are some of the activities you can do at Perth Paradise Resort. Below are the rates for each activity.\r\n\r\nKayaking – PHP250.00\r\nSnorkelong – PHP250.00\r\nScuba Diving – PHP1,600.00 (for reservation only)', 'optimization', 'uploads/drone-shot-where-you.jpg', 0, '2024-12-20 01:35:16', NULL, NULL),
(16, 'Seda Lio: El Nido’s Sustainable 5-Star Resort Reviewed', 'Tucked away from El Nido town on Lio beach is Seda Lio, the first sustainable 5-star resort in the Philippines. They pride themselves on responsible eco-tourism and sustainability in a country that is already feeling the effects of climate change. While these terms are often misused to appeal to certain clientele, it’s always great to come across an establishment that actually puts these ideas into practice. Seda Lio offers their guests luxury, relaxation and a peaceful haven away from the hustle and bustle to explore this stunning island from.\r\nGetting To Lio\r\nWe spent 4 nights here and flew directly to Lio via AirSwift. I would highly recommend this over going through the tedious expedition of landing in Puerto Princesa and dealing with at least 6 hours of onward travel to get to Lio.', 'optimization', 'uploads/Seda_Lio_El_Nido_Reviewed-1-1440x1080.jpg', 0, '2024-12-20 01:36:02', NULL, NULL),
(17, 'Review: Bagobo Beach Resort in Panglao Island, Bohol', 'The Poor Traveler had always wanted to go to Bohol as this extremely popular tourist destination is one of the top 3 provinces I’d die to visit. That’s why there was really no word to describe my excitement when I was planning this trip.\r\n\r\nPlanning was easy. Initially, we wanted to check in at Bohol Beach Club but I realized I was the “Poor” Traveler so I had to look for something more budget-friendly. After all, it is in the core principles of this blog that I pursue searching for the cheapest yet worth-it accommodations and travel packages for the benefit of the readers.\r\nAfter hours of googling and asking my friends who already had been to Bohol, I finally decided where to stay — Bagobo Beach Resort in Panglao Island. Arranging it was a piece of cake. We just contacted Bagobo Beach Resort via its owner and manager Tess on Facebook and she sent us the itinerary and the quote a day later. Bagobo Beach Resort offers not just accommodations but also several tours that are customizable to fit your travel needs and budget.\r\n\r\nThere would be 8 of us in the group and that’s a good thing. And we were really shocked by the quote we received from Bagobo — in a good way.\r\nThe Itinerary\r\nBagobo Beach Resort would only charge us P3200 per person for our 4-day, 3-night stay inclusive of the following. Please note that we were a group of 8. Remember: In traveling, the more, the cheaper.\r\n\r\n2 air-conditioned rooms – Bungalow style with private bathroom\r\n3 Breakfast meals\r\n3 Dinners (exclusive of drinks)\r\nLand / countryside tour\r\nSea tour (Dolphin watching and visit to Balicasag Island and Virgin Island)\r\nTransfers\r\nFree use of Karaoke at the restaurant\r\nFree use of wireless Internet (wi-fi)\r\nWelcome Drinks (fresh home made)\r\nHere’s the itinerary for the tour:\r\n\r\nDay 1: COUNTRYSIDE TOUR (whole day)\r\n\r\nChocolate Hills (Carmen, Bohol)\r\nManmade forest (Bilar)\r\nBohol Butterfly Sanctuaries (Bilar)\r\nLoboc River Cruise (Loboc) Note: Exclusive Lunch Buffe at Loboc river\r\nHanging Bridge\r\nTarsier Encounter\r\n‘Prony’ – the famous Bohol Python(Albur)\r\nBaclayon Church (Baclayon)\r\nBlood Compact Site (Tagbilaran City)\r\nHinagdanan Cave (Panglao)\r\nDay 2: SEA TOUR (whole day trip)\r\n\r\nBalicasag Island,\r\nVirgin Island\r\nDolphin Watching\r\nBEACH\r\nDAY 3: RELAX DAY (just stay at the resort. But if you wish to go elsewhere, you can arrange it with Bagobo, too.)\r\n\r\nDAY 4: Departure (since our flight was early in the morning)\r\n\r\nThe driver picked us up at the Tagbilaran airport. He was very friendly, throwing jokes every once in a while. Since we were already in Tagbilaran and the resort is in Panglao Island, we started the tour even before checking in. We actually had laptops with us but the driver didn’t leave the vehicle so they were safe. We never had any problems with security. We didn’t lose anything whatsoever.\r\n\r\nI’ll discuss the different places we have visited in separate posts. But that day was sooo long that it was already dark when we arrived at the resort. (Actually, we convinced — more like forced— the driver to stop at the market for a while so we could buy more food and booze. LOL.)\r\n\r\nThe Resort\r\nWhen we arrived, we were greeted by Tessa. We ordered dinner (which was part of the package) and got the keys to our rooms.\r\n\r\nThis is the part where I have to warn you about expectations. You see, sometimes I get sick of tourists who just keep on complaining about anything. Here’s what you should remember, this is a budget resort. BUDGET being the operative word. Don’t expect 5-star accommodations or swimming pools with an acoustic band singing in the background.\r\n\r\nThe Poor Traveler and his gang weren’t expecting anything like that because if we wanted that, we’d be checking in at Bohol Beach Club and let our pockets bleed. Besides, maybe I’m the type of traveler who really doesn’t care about accommodations that much unless there’s a major major blunder. To me, the TOUR weighs so much heavier than the room wherein all I would do is sleep. But that’s just me.\r\nBagobo Beach Resort was just right (and probably better) for the cost. The rooms and the bathrooms were clean, albeit small. Tessa was attentive to our needs although I felt like the resort was under-staffed. The water in the faucet was a bit salty so you should probably bring your own water.\r\n\r\nEven the restaurant served good food for P150-P300 per meal. At first we thought it was expensive but when they were served, goodness gracious, really generous servings. Like each serving is good to 2-3 people.\r\n\r\nThe Beach\r\nSince we arrived at the resort in the evening, it wasn’t until the next morning when we finally saw the beach. As expected, it was beautiful. The best thing about it was that it was just us in our area. We started walking along the beach and saw the other beach resorts crowded, very crowded. It was a good thing Bagobo was at the far end of the beach, far from all the noise from the other resorts in the area. We actually felt like it was ours.\r\n\r\nThe berm of the beach was strewn with small rocks, pebbles and shells but once you go past it, you’ll be greeted by the finest pearly white sand you’ll ever see in your life. They were more like chalk than sand. It was really that fine.', 'optimization', 'uploads/Bohol.jpg', 0, '2024-12-20 01:42:51', NULL, NULL),
(21, 'BOHOL!', 'SKSKSK!', 'optimization', 'uploads/465768110_1266654457933426_6760798930402049303_n.jpg', 0, '2024-12-20 06:59:56', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `registration_date`) VALUES
(5, 'emman', '123', 'admin', '2024-12-12 08:27:26'),
(9, '123', '123', 'user', '2024-12-15 04:49:46'),
(10, 'john', 'john', 'user', '2024-12-19 15:02:12'),
(11, 'GIN', '123', 'user', '2024-12-20 06:06:18'),
(12, '321', '321', 'user', '2024-12-20 07:51:19');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_comment_per_post` (`user_id`,`post_id`),
  ADD KEY `post_id` (`post_id`);

--
-- Indexes for table `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `post_id` (`post_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `postimages`
--
ALTER TABLE `postimages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_category` (`category_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `likes`
--
ALTER TABLE `likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `postimages`
--
ALTER TABLE `postimages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `likes`
--
ALTER TABLE `likes`
  ADD CONSTRAINT `likes_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `likes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `postimages`
--
ALTER TABLE `postimages`
  ADD CONSTRAINT `postimages_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `fk_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

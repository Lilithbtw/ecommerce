-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `is_admin` BOOLEAN NOT NULL DEFAULT 0,
  `name` VARCHAR(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `products`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `products` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `price` DECIMAL(10, 2) NOT NULL,
  `image_path` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `orders`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `total` DECIMAL(10, 2) NOT NULL,
  `status` VARCHAR(50) NOT NULL, -- e.g., 'pending', 'completed', 'shipped'
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `order_items`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) NOT NULL,
  `product_id` INT(11) NOT NULL,
  `quantity` INT(11) NOT NULL,
  `price` DECIMAL(10, 2) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Insert initial data
-- --------------------------------------------------------

-- Insert mockup products using INSERT IGNORE to safely run the script multiple times.
INSERT IGNORE INTO `products` (`id`, `name`, `description`, `price`, `image_path`) VALUES
(1, 'Cyberpunk Headphones', 'A sleek, noise-cancelling headset with RGB lighting and immersive 7.1 surround sound. Perfect for gaming and design work.', 199.99, 'https://placehold.co/400x400/000/fff?text=Headphones'),
(2, 'Vintage Leather Journal', 'Hand-stitched leather journal with 200 pages of acid-free, archival paper. Ideal for writers and artists.', 45.50, 'https://placehold.co/400x400/964B00/fff?text=Journal'),
(3, 'Smart Mug Warmer', 'Keep your coffee or tea at the perfect temperature all day. Includes an auto-shutoff feature for safety.', 29.95, 'https://placehold.co/400x400/333/fff?text=Mug+Warmer'),
(4, 'Zen Garden Kit', 'A small, desktop Japanese Zen Garden for stress relief and meditation. Includes sand, rake, and stones.', 19.99, 'https://placehold.co/400x400/777/fff?text=Zen+Garden');
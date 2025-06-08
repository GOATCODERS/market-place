CREATE TABLE Users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    surname VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100) NULL,
    role ENUM('buyer', 'seller', 'admin' ,'customer') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);


CREATE TABLE listings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    item_name VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    item_condition VARCHAR(100) NOT NULL,
    purchase_date DATE DEFAULT NULL,
    price DECIMAL(10, 2) NOT NULL,
    description TEXT NOT NULL,
    image LONGBLOB, -- store binary image here
    image_type VARCHAR(50), -- e.g. image/jpeg
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create messages table to store chat messages
CREATE TABLE chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    listing_id INT NOT NULL,
    message TEXT NOT NULL,
    read_status BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE CASCADE
);

-- Create chat sessions table to track conversations
CREATE TABLE chat_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    listing_id INT NOT NULL,
    buyer_id INT NOT NULL,
    seller_id INT NOT NULL, 
    last_message_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE CASCADE,
    UNIQUE KEY unique_chat (listing_id, buyer_id, seller_id)
);

-- The `Users` table stores user information, including their role (buyer, seller, admin, customer).
-- The `listings` table stores information about items listed for sale, including the user who listed it.


CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(64) UNIQUE NOT NULL,
    name VARCHAR(64) NOT NULL,
    surname VARCHAR(64) NOT NULL,
    email VARCHAR(128) UNIQUE NOT NULL,
    phone VARCHAR(20),
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(16) NOT NULL DEFAULT 'user',
    created_at TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE TABLE districts (
    id SERIAL PRIMARY KEY,
    name VARCHAR(128) NOT NULL,
    description TEXT,
    area NUMERIC(10,2),
    population INT,
    founded_year INT,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMP NOT NULL DEFAULT NOW(),
    image VARCHAR(255)
);

CREATE TABLE streets (
    id SERIAL PRIMARY KEY,
    name VARCHAR(128) NOT NULL,
    postal_code VARCHAR(16),
    built_year INT,
    length_km NUMERIC(10,2),
    surface_type VARCHAR(64),
    district_id INT NOT NULL REFERENCES districts(id) ON DELETE CASCADE,
    image VARCHAR(255)
);

CREATE TABLE institutions (
    id SERIAL PRIMARY KEY,
    name VARCHAR(128) NOT NULL,
    institution_type VARCHAR(64),
    address VARCHAR(255),
    phone VARCHAR(20),
    working_hours VARCHAR(128),
    district_id INT NOT NULL REFERENCES districts(id) ON DELETE CASCADE,
    image VARCHAR(255)
);

CREATE TABLE events (
    id SERIAL PRIMARY KEY,
    title VARCHAR(128) NOT NULL,
    event_date DATE NOT NULL,
    location VARCHAR(255),
    description TEXT,
    organizer VARCHAR(128),
    visitors_count INT,
    district_id INT NOT NULL REFERENCES districts(id) ON DELETE CASCADE,
    image VARCHAR(255)
);

CREATE TABLE tickets (
    id SERIAL PRIMARY KEY,
    event_id INT NOT NULL REFERENCES events(id) ON DELETE CASCADE,
    price NUMERIC(10,2) NOT NULL,
    total_quantity INT NOT NULL,
    sold_quantity INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE TABLE purchases (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    ticket_id INT NOT NULL REFERENCES tickets(id) ON DELETE CASCADE,
    quantity INT NOT NULL DEFAULT 1,
    total_price NUMERIC(10,2) NOT NULL,
    purchased_at TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE TABLE districts_log (
    id SERIAL PRIMARY KEY,
    district_id INT,
    action VARCHAR(16) NOT NULL,
    changed_at TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE TABLE apartments (
    id SERIAL PRIMARY KEY,
    district_id INT NOT NULL REFERENCES districts(id) ON DELETE CASCADE,
    street_id INT REFERENCES streets(id) ON DELETE SET NULL,
    floor INT,
    rooms INT,
    area_m2 NUMERIC(10,2),
    price_per_day NUMERIC(10,2) NOT NULL,
    is_available BOOLEAN NOT NULL DEFAULT TRUE,
    description TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    image VARCHAR(255)
);

CREATE TABLE apartment_reservations (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    apartment_id INT NOT NULL REFERENCES apartments(id) ON DELETE CASCADE,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    total_price NUMERIC(10,2) NOT NULL,
    status VARCHAR(16) NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE TABLE cart (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    item_id INT NOT NULL,
    type VARCHAR(20) NOT NULL,
    title VARCHAR(255) NOT NULL,
    price NUMERIC(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT NOW(),
    quantity INT DEFAULT 1,
    days INT DEFAULT NULL,
    total DECIMAL(10,2) DEFAULT 0
);
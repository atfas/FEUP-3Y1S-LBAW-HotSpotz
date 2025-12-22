
SET search_path TO lbaw2581;

INSERT INTO admin_notification (type) VALUES
('NewHostApplication'),
('ListingReported'),
('UserSuspended');

INSERT INTO users (id, full_name, email, password_hash, date_of_birth, phone, is_admin) VALUES
(1, 'Admin Staff', 'admin@stay.com', '$2y$12$uMUDL9XOjwMsw92AgbBqXO1UzASzOxndiHAXhY20FgIyhIVV7lgiy', '1990-01-01', '+111222333', true),
(2, 'Super Admin', 'super@stay.com', '$2y$12$uMUDL9XOjwMsw92AgbBqXO1UzASzOxndiHAXhY20FgIyhIVV7lgiy', '1985-05-10', '+144555666', true);


INSERT INTO users (id, full_name, email, password_hash, date_of_birth, phone) VALUES
(3, 'Alice Green', 'alice@guest.com', '$2y$12$uMUDL9XOjwMsw92AgbBqXO1UzASzOxndiHAXhY20FgIyhIVV7lgiy', '1995-04-12', '+111111111'),
(4, 'Bob White', 'bob@guest.com', '$2y$12$uMUDL9XOjwMsw92AgbBqXO1UzASzOxndiHAXhY20FgIyhIVV7lgiy', '2000-07-20', '+222222222'),
(5, 'Charlie Black', 'charlie@guest.com', '$2y$12$uMUDL9XOjwMsw92AgbBqXO1UzASzOxndiHAXhY20FgIyhIVV7lgiy', '1998-11-30', '+333333333');

INSERT INTO users (id, full_name, email, password_hash, date_of_birth, phone, application_status, answers, verification_docs, registration_date, submitted_at) VALUES
(6, 'David Host', 'david@host.com', '$2y$12$uMUDL9XOjwMsw92AgbBqXO1UzASzOxndiHAXhY20FgIyhIVV7lgiy', '1992-02-15', '+444444444', 'Pending', 'My answers to the questions.', 'path/to/docs_david.pdf', '2025-01-09 09:00:00', '2025-01-10 09:00:00'),
(7, 'Eve Owner', 'eve@host.com', '$2y$12$uMUDL9XOjwMsw92AgbBqXO1UzASzOxndiHAXhY20FgIyhIVV7lgiy', '1988-06-25', '+555555555', 'Pending', 'I have a great property.', 'path/to/docs_eve.pdf', '2025-01-10 10:00:00', '2025-01-11 10:00:00'),
(8, 'Frank Landlord', 'frank@host.com', '$2y$12$uMUDL9XOjwMsw92AgbBqXO1UzASzOxndiHAXhY20FgIyhIVV7lgiy', '1990-09-05', '+666666666', 'Pending', 'Here are my details.', 'path/to/docs_frank.pdf', '2025-01-11 11:00:00', '2025-01-12 11:00:00'),
(9, 'Grace Property', 'grace@host.com', '$2y$12$uMUDL9XOjwMsw92AgbBqXO1UzASzOxndiHAXhY20FgIyhIVV7lgiy', '1993-01-18', '+777777777', 'Pending', 'My answers.', 'path/to/docs_grace.pdf', '2025-01-12 12:00:00', '2025-01-13 12:00:00'),
(10, 'Heidi Home', 'heidi@host.com', '$2y$12$uMUDL9XOjwMsw92AgbBqXO1UzASzOxndiHAXhY20FgIyhIVV7lgiy', '1985-03-22', '+888888888', 'Pending', 'Application answers.', 'path/to/docs_heidi.pdf', '2025-01-13 13:00:00', '2025-01-14 13:00:00');

INSERT INTO users (id, full_name, email, password_hash, date_of_birth, phone, application_status, answers, verification_docs, registration_date, submitted_at, verified_at, verifier_id, payout_details) VALUES
(11, 'Isaac Newton', 'isaac@host.com', '$2y$12$uMUDL9XOjwMsw92AgbBqXO1UzASzOxndiHAXhY20FgIyhIVV7lgiy', '1975-04-04', '+101010101', 'Approved', 'The Best answers.', 'path/to/docs_isac.pdf', '2025-01-01 08:00:00', '2025-01-02 08:00:00', '2025-01-15 10:05:00', 1, '{"bank":"Math Bank","account":"666666"}'),
(12, 'Julia Roberts', 'julia@guest.com', '$2y$12$uMUDL9XOjwMsw92AgbBqXO1UzASzOxndiHAXhY20FgIyhIVV7lgiy', '1990-08-15', '+121212121', NULL, NULL, NULL, '2025-01-03 09:00:00', NULL, NULL, NULL, NULL),
(13, 'Kevin Space', 'kevin@guest.com', '$2y$12$uMUDL9XOjwMsw92AgbBqXO1UzASzOxndiHAXhY20FgIyhIVV7lgiy', '1992-09-10', '+131313131', NULL, NULL, NULL, '2025-01-04 09:30:00', NULL, NULL, NULL, NULL),
(14, 'Laura Palmer', 'laura@host.com', '$2y$12$uMUDL9XOjwMsw92AgbBqXO1UzASzOxndiHAXhY20FgIyhIVV7lgiy', '1987-03-03', '+141414141', 'Approved', 'The Second best answers.', 'path/to/docs_laura.pdf', '2025-01-05 10:00:00', '2025-01-06 10:00:00', '2025-01-15 10:06:00', 1, '{"bank":"Pacific Bank","account":"777777"}'),
(15, 'Mark Benson', 'mark@guest.com', '$2y$12$uMUDL9XOjwMsw92AgbBqXO1UzASzOxndiHAXhY20FgIyhIVV7lgiy', '1993-11-11', '+151515151', NULL, NULL, NULL, '2025-01-06 11:00:00', NULL, NULL, NULL, NULL);

UPDATE users SET profile_picture_url = 'https://images.pexels.com/photos/774909/pexels-photo-774909.jpeg?cs=srgb&dl=pexels-olly-774909.jpg&fm=jpg' WHERE id = 3;
UPDATE users SET profile_picture_url = 'https://st2.depositphotos.com/4157265/49594/i/450/depositphotos_495947944-stock-photo-headshot-portrait-of-smiling-young.jpg' WHERE id = 12;
UPDATE users SET profile_picture_url = 'https://t3.ftcdn.net/jpg/06/99/46/60/360_F_699466075_DaPTBNlNQTOwwjkOiFEoOvzDV0ByXR9E.jpg' WHERE id = 4;
UPDATE users SET profile_picture_url = 'https://www.shutterstock.com/image-photo/profile-picture-smiling-successful-young-260nw-2040223583.jpg' WHERE id = 5;

BEGIN;
UPDATE users
SET
    application_status = 'Approved',
    verified_at = '2025-01-15 10:00:00',
    verifier_id = 1, 
    payout_details = '{"bank": "Global Bank", "account": "111111"}'
WHERE
    id = 6
    AND application_status = 'Pending';
COMMIT;

BEGIN;
UPDATE users
SET
    application_status = 'Approved',
    verified_at = '2025-01-15 10:01:00',
    verifier_id = 1,
    payout_details = '{"bank": "City Bank", "account": "222222"}'
WHERE
    id = 7
    AND application_status = 'Pending';
COMMIT;

BEGIN;
UPDATE users
SET
    application_status = 'Approved',
    verified_at = '2025-01-15 10:02:00',
    verifier_id = 2,
    payout_details = '{"paypal": "frank@host.com"}'
WHERE
    id = 8
    AND application_status = 'Pending';
COMMIT;

BEGIN;
UPDATE users
SET
    application_status = 'Approved',
    verified_at = '2025-01-15 10:03:00',
    verifier_id = 2,
    payout_details = '{"bank": "National Bank", "account": "444444"}'
WHERE
    id = 9
    AND application_status = 'Pending';
COMMIT;

BEGIN;
UPDATE users
SET
    application_status = 'Approved',
    verified_at = '2025-01-15 10:04:00',
    verifier_id = 1,
    payout_details = '{"bank": "First Bank", "account": "555555"}'
WHERE
    id = 10
    AND application_status = 'Pending';
COMMIT;

BEGIN;
DO $$
DECLARE
    new_listing_id INT;
BEGIN
    INSERT INTO listings (id, title, description, max_occupancy, location_address, minimum_stay, cancellation_policy, amenities, host_id)
    VALUES (1, 'Cozy Downtown Apartment', 'A beautiful apartment in the heart of the city.', 2, '123 Main St, Cityville', 2, 'Flexible: Full refund 1 day prior to arrival.', 'wifi,heating', 6)
    RETURNING id INTO new_listing_id; 

    INSERT INTO listing_photos (url, listing_id) VALUES ('/storage/listing_photos/1_1.avif', new_listing_id);
    INSERT INTO listing_photos (url, listing_id) VALUES ('/storage/listing_photos/1_2.webp', new_listing_id); 
    INSERT INTO listing_photos (url, listing_id) VALUES ('/storage/listing_photos/1_3.webp', new_listing_id); 
    INSERT INTO listing_photos (url, listing_id) VALUES ('/storage/listing_photos/1_4.jpg', new_listing_id);
END $$;
COMMIT;

BEGIN;
DO $$
DECLARE
    new_listing_id INT;
BEGIN
    INSERT INTO listings (id, title, description, max_occupancy, location_address, minimum_stay, cancellation_policy, amenities, host_id)
    VALUES (2, 'Sunny Beachside Villa', 'Stunning views of the ocean, perfect for a getaway.', 8, '456 Ocean Ave, Beachtown', 5, 'Strict: 50% refund up to 1 week prior to arrival.', 'wifi,pool,beachfront', 7)
    RETURNING id INTO new_listing_id; 

    INSERT INTO listing_photos (url, listing_id) VALUES ('/storage/listing_photos/2_1.jpg', new_listing_id);
    INSERT INTO listing_photos (url, listing_id) VALUES ('/storage/listing_photos/2_2.jpeg', new_listing_id); 
    INSERT INTO listing_photos (url, listing_id) VALUES ('/storage/listing_photos/2_3.jpeg', new_listing_id); 
    INSERT INTO listing_photos (url, listing_id) VALUES ('/storage/listing_photos/2_4.jpg', new_listing_id);
END $$;
COMMIT;

BEGIN;
DO $$
DECLARE
    new_listing_id INT;
BEGIN
    INSERT INTO listings (id, title, description, max_occupancy, location_address, minimum_stay, cancellation_policy, amenities, host_id)
    VALUES (3, 'Rustic Mountain Cabin', 'Escape to the wilderness. No Wi-Fi, pure nature.', 4, '789 Pine Rd, Mountainside', 3, 'Moderate: Full refund 5 days prior to arrival.', 'fireplace,parking', 8)
    RETURNING id INTO new_listing_id; 

    INSERT INTO listing_photos (url, listing_id) VALUES ('/storage/listing_photos/3_1.jpg', new_listing_id);
    INSERT INTO listing_photos (url, listing_id) VALUES ('/storage/listing_photos/3_2.jpg', new_listing_id); 
    INSERT INTO listing_photos (url, listing_id) VALUES ('/storage/listing_photos/3_3.png', new_listing_id);
    INSERT INTO listing_photos (url, listing_id) VALUES ('/storage/listing_photos/3_4.jpg', new_listing_id); 
END $$;
COMMIT;

BEGIN;
DO $$
DECLARE
    new_listing_id INT;
BEGIN
    INSERT INTO listings (id, title, description, max_occupancy, location_address, minimum_stay, cancellation_policy, amenities, host_id)
    VALUES (4, 'Modern City Loft', 'A stylish loft with all modern amenities.', 3, '321 Urban Way, Metropolis', 2, 'Flexible: Full refund 1 day prior to arrival.', 'wifi,ac,washer', 9)
    RETURNING id INTO new_listing_id; 

    INSERT INTO listing_photos (url, listing_id) VALUES ('/storage/listing_photos/4_1.jpg', new_listing_id);
    INSERT INTO listing_photos (url, listing_id) VALUES ('/storage/listing_photos/4_2.jpg', new_listing_id); 
    INSERT INTO listing_photos (url, listing_id) VALUES ('/storage/listing_photos/4_3.jpg', new_listing_id); 
    INSERT INTO listing_photos (url, listing_id) VALUES ('/storage/listing_photos/4_4.png', new_listing_id);
END $$;
COMMIT;

BEGIN;
DO $$
DECLARE
    new_listing_id INT;
BEGIN
    INSERT INTO listings (id, title, description, max_occupancy, location_address, minimum_stay, cancellation_policy, amenities, host_id)
    VALUES (5, 'Quiet Suburban House', 'Perfect for families. Large backyard and grill.', 6, '654 Suburb Ln, Greendale', 4, 'Moderate: Full refund 5 days prior to arrival.', 'wifi,parking,garden', 10)
    RETURNING id INTO new_listing_id; 

    INSERT INTO listing_photos (url, listing_id) VALUES ('/storage/listing_photos/5_1.jpg', new_listing_id);
    INSERT INTO listing_photos (url, listing_id) VALUES ('/storage/listing_photos/5_2.jpg', new_listing_id); 
    INSERT INTO listing_photos (url, listing_id) VALUES ('/storage/listing_photos/5_3.jpg', new_listing_id);
    INSERT INTO listing_photos (url, listing_id) VALUES ('/storage/listing_photos/5_4.jpg', new_listing_id); 
END $$;
COMMIT;

INSERT INTO availability VALUES
(1, '2025-12-05', '2025-12-20', 120.00, '15:00', '11:00', 1),
(2, '2026-01-05', '2026-01-31', 125.00, '15:00', '11:00', 1),
(3, '2025-12-10', '2025-12-20', 350.00, '16:00', '10:00', 2),
(4, '2026-01-05', '2026-01-20', 350.00, '16:00', '10:00', 2),
(5, '2025-12-01', '2026-01-15', 90.00, '14:00', '12:00', 3),
(6, '2026-02-01', '2026-03-15', 95.00, '14:00', '12:00', 3),
(7, '2025-12-15', '2026-01-15', 180.00, '15:00', '11:00', 4),
(8, '2026-01-01', '2026-01-31', 210.00, '15:00', '11:00', 5);


BEGIN;
DO $$
DECLARE new_listing_id INT;
BEGIN
    INSERT INTO listings (id, title, description, max_occupancy, location_address, minimum_stay, cancellation_policy, amenities, host_id)
    VALUES (6, 'Seaside Retreat', 'A comfortable seaside home with panoramic ocean views and a sun terrace.', 6, '12 Shoreline Dr, Oceanview', 3, 'Moderate: Full refund 7 days prior to arrival.', 'wifi,pool,beachfront', 11)
    RETURNING id INTO new_listing_id;
    INSERT INTO listing_photos (url, listing_id) VALUES ('/storage/listing_photos/6_1.avif', new_listing_id);
    INSERT INTO listing_photos (url, listing_id) VALUES ('/storage/listing_photos/6_2.webp', new_listing_id);
    INSERT INTO listing_photos (url, listing_id) VALUES ('/storage/listing_photos/6_3.webp', new_listing_id);
    INSERT INTO listing_photos (url, listing_id) VALUES ('/storage/listing_photos/6_4.jpg', new_listing_id);
END $$;
COMMIT;

BEGIN;
DO $$
DECLARE new_listing_id INT;
BEGIN
    INSERT INTO listings (id, title, description, max_occupancy, location_address, minimum_stay, cancellation_policy, amenities, host_id)
    VALUES (7, 'Lakeview Cottage', 'Cozy cottage overlooking the lake, ideal for quiet getaways.', 4, '88 Lake Rd, Lakeside', 2, 'Flexible: Full refund 2 days prior to arrival.', 'fireplace,parking', 14)
    RETURNING id INTO new_listing_id;
    INSERT INTO listing_photos (url, listing_id) VALUES ('/storage/listing_photos/7_1.jpg', new_listing_id);
    INSERT INTO listing_photos (url, listing_id) VALUES ('/storage/listing_photos/7_2.jpeg', new_listing_id);
    INSERT INTO listing_photos (url, listing_id) VALUES ('/storage/listing_photos/7_3.jpeg', new_listing_id);
    INSERT INTO listing_photos (url, listing_id) VALUES ('/storage/listing_photos/7_4.jpg', new_listing_id);
END $$;
COMMIT;

BEGIN; DO $$ DECLARE new_listing_id INT; BEGIN INSERT INTO listings (id, title, description, max_occupancy, location_address, minimum_stay, cancellation_policy, amenities, host_id) VALUES (8, 'Countryside Manor', 'Spacious manor with private garden and easy access to countryside trails.', 10, '200 Country Lane, Meadowbrook', 4, 'Strict: 50% refund up to 14 days prior to arrival.', 'garden,parking,wifi', 6) RETURNING id INTO new_listing_id; INSERT INTO listing_photos (url, listing_id) VALUES ('/storage/listing_photos/8_1.jpg', new_listing_id); INSERT INTO listing_photos (url, listing_id) VALUES ('/storage/listing_photos/8_2.jpg', new_listing_id); INSERT INTO listing_photos (url, listing_id) VALUES ('/storage/listing_photos/8_3.png', new_listing_id); INSERT INTO listing_photos (url, listing_id) VALUES ('/storage/listing_photos/8_4.jpg', new_listing_id); END $$; COMMIT;

BEGIN; DO $$ DECLARE new_listing_id INT; BEGIN INSERT INTO listings (id, title, description, max_occupancy, location_address, minimum_stay, cancellation_policy, amenities, host_id) VALUES (9, 'Downtown Studio', 'Compact studio in the city center, close to public transport and cafes.', 2, '5 Central Ave, Metropolis', 1, 'Flexible: Full refund 1 day prior to arrival.', 'wifi,studio', 7) RETURNING id INTO new_listing_id; INSERT INTO listing_photos (url, listing_id) VALUES ('/storage/listing_photos/9_1.jpg', new_listing_id); INSERT INTO listing_photos (url, listing_id) VALUES ('/storage/listing_photos/9_2.jpg', new_listing_id); INSERT INTO listing_photos (url, listing_id) VALUES ('/storage/listing_photos/9_3.jpg', new_listing_id); INSERT INTO listing_photos (url, listing_id) VALUES ('/storage/listing_photos/9_4.png', new_listing_id); END $$; COMMIT;

BEGIN; DO $$ DECLARE new_listing_id INT; BEGIN INSERT INTO listings (id, title, description, max_occupancy, location_address, minimum_stay, cancellation_policy, amenities, host_id) VALUES (10, 'Historic Townhouse', 'Charming townhouse with period features and modern comforts.', 5, '14 Baker St, Oldtown', 2, 'Moderate: Full refund 5 days prior to arrival.', 'wifi,parking,washer', 8) RETURNING id INTO new_listing_id; INSERT INTO listing_photos (url, listing_id) VALUES ('/storage/listing_photos/10_1.jpg', new_listing_id); INSERT INTO listing_photos (url, listing_id) VALUES ('/storage/listing_photos/10_2.jpg', new_listing_id); INSERT INTO listing_photos (url, listing_id) VALUES ('/storage/listing_photos/10_3.jpg', new_listing_id); INSERT INTO listing_photos (url, listing_id) VALUES ('/storage/listing_photos/10_4.jpg', new_listing_id); END $$; COMMIT;

BEGIN; DO $$ DECLARE new_listing_id INT; BEGIN INSERT INTO listings (id, title, description, max_occupancy, location_address, minimum_stay, cancellation_policy, amenities, host_id) VALUES (11, 'Garden Flat', 'Bright flat with access to a shared garden and local markets nearby.', 3, '22 Orchard Ave, Greenfield', 2, 'Flexible: Full refund 1 day prior to arrival.', 'garden,wifi', 9) RETURNING id INTO new_listing_id; INSERT INTO listing_photos (url, listing_id) VALUES ('/storage/listing_photos/11_1.avif', new_listing_id); INSERT INTO listing_photos (url, listing_id) VALUES ('/storage/listing_photos/11_2.webp', new_listing_id); INSERT INTO listing_photos (url, listing_id) VALUES ('/storage/listing_photos/11_3.webp', new_listing_id); INSERT INTO listing_photos (url, listing_id) VALUES ('/storage/listing_photos/11_4.jpg', new_listing_id); END $$; COMMIT;

BEGIN; DO $$ DECLARE new_listing_id INT; BEGIN INSERT INTO listings (id, title, description, max_occupancy, location_address, minimum_stay, cancellation_policy, amenities, host_id) VALUES (12, 'Penthouse Suite', 'Luxury penthouse with skyline views and rooftop access.', 4, '350 Skyline Blvd, Uptown', 2, 'Strict: 50% refund up to 7 days prior to arrival.', 'wifi,ac,washer', 11) RETURNING id INTO new_listing_id; INSERT INTO listing_photos (url, listing_id) VALUES ('/storage/listing_photos/12_1.jpg', new_listing_id); INSERT INTO listing_photos (url, listing_id) VALUES ('/storage/listing_photos/12_2.jpeg', new_listing_id); INSERT INTO listing_photos (url, listing_id) VALUES ('/storage/listing_photos/12_3.jpeg', new_listing_id); INSERT INTO listing_photos (url, listing_id) VALUES ('/storage/listing_photos/12_4.jpg', new_listing_id); END $$; COMMIT;

BEGIN; DO $$ DECLARE new_listing_id INT; BEGIN INSERT INTO listings (id, title, description, max_occupancy, location_address, minimum_stay, cancellation_policy, amenities, host_id) VALUES (13, 'Urban Loft', 'Spacious loft with industrial character and nearby galleries.', 4, '77 Industrial Rd, Cityquarter', 2, 'Flexible: Full refund 1 day prior to arrival.', 'loft,wifi', 14) RETURNING id INTO new_listing_id; INSERT INTO listing_photos (url, listing_id) VALUES ('/storage/listing_photos/13_1.jpg', new_listing_id); INSERT INTO listing_photos (url, listing_id) VALUES ('/storage/listing_photos/13_2.jpg', new_listing_id); INSERT INTO listing_photos (url, listing_id) VALUES ('/storage/listing_photos/13_3.png', new_listing_id); INSERT INTO listing_photos (url, listing_id) VALUES ('/storage/listing_photos/13_4.jpg', new_listing_id); END $$; COMMIT;

BEGIN; DO $$ DECLARE new_listing_id INT; BEGIN INSERT INTO listings (id, title, description, max_occupancy, location_address, minimum_stay, cancellation_policy, amenities, host_id) VALUES (14, 'Hillside Bungalow', 'Sunny bungalow with deck and easy hiking access.', 5, '9 Hillcrest Ave, Ridgeview', 3, 'Moderate: Full refund 5 days prior to arrival.', 'parking,garden', 6) RETURNING id INTO new_listing_id; INSERT INTO listing_photos (url, listing_id) VALUES ('/storage/listing_photos/14_1.jpg', new_listing_id); INSERT INTO listing_photos (url, listing_id) VALUES ('/storage/listing_photos/14_2.jpg', new_listing_id); INSERT INTO listing_photos (url, listing_id) VALUES ('/storage/listing_photos/14_3.jpg', new_listing_id); INSERT INTO listing_photos (url, listing_id) VALUES ('/storage/listing_photos/14_4.png', new_listing_id); END $$; COMMIT;

BEGIN; DO $$ DECLARE new_listing_id INT; BEGIN INSERT INTO listings (id, title, description, max_occupancy, location_address, minimum_stay, cancellation_policy, amenities, host_id) VALUES (15, 'Cozy Annex', 'Small annex ideal for solo travellers, quiet neighbourhood.', 2, '3 Maple St, Smalltown', 1, 'Flexible: Full refund 1 day prior to arrival.', 'wifi', 9) RETURNING id INTO new_listing_id; INSERT INTO listing_photos (url, listing_id) VALUES ('/storage/listing_photos/15_1.jpg', new_listing_id); INSERT INTO listing_photos (url, listing_id) VALUES ('/storage/listing_photos/15_2.jpg', new_listing_id); INSERT INTO listing_photos (url, listing_id) VALUES ('/storage/listing_photos/15_3.jpg', new_listing_id); INSERT INTO listing_photos (url, listing_id) VALUES ('/storage/listing_photos/15_4.jpg', new_listing_id); END $$; COMMIT;


INSERT INTO availability (id, start_date, end_date, price_per_night, check_in_time, check_out_time, listing_id) VALUES
(9, '2025-12-01', '2025-12-07', 200.00, '15:00', '11:00', 6),
(10, '2025-12-08', '2025-12-14', 210.00, '15:00', '11:00', 6),
(11, '2025-12-15', '2025-12-21', 220.00, '15:00', '11:00', 6),
(12, '2025-12-22', '2025-12-28', 230.00, '15:00', '11:00', 6),
(13, '2025-12-29', '2026-01-04', 240.00, '15:00', '11:00', 6),
(14, '2026-01-05', '2026-01-11', 250.00, '15:00', '11:00', 6),
(15, '2026-01-12', '2026-01-18', 260.00, '15:00', '11:00', 6),
(16, '2026-01-19', '2026-01-25', 270.00, '15:00', '11:00', 6),
(17, '2026-01-26', '2026-02-01', 280.00, '15:00', '11:00', 6),
(18, '2026-02-02', '2026-02-08', 290.00, '15:00', '11:00', 6),
(19, '2026-02-09', '2026-02-15', 300.00, '15:00', '11:00', 6),
(20, '2026-03-01', '2026-03-07', 160.00, '14:00', '12:00', 7),
(21, '2026-03-08', '2026-03-14', 165.00, '14:00', '12:00', 8),
(22, '2026-03-10', '2026-03-20', 180.00, '16:00', '10:00', 9),
(23, '2026-04-01', '2026-04-10', 300.00, '15:00', '11:00', 10),
(24, '2026-05-01', '2026-05-10', 140.00, '14:00', '12:00', 11),
(25, '2026-06-01', '2026-06-10', 450.00, '15:00', '11:00', 12),
(26, '2026-07-01', '2026-07-10', 130.00, '14:00', '12:00', 13),
(27, '2026-08-01', '2026-08-10', 155.00, '15:00', '11:00', 14),
(28, '2026-09-01', '2026-09-10', 95.00, '14:00', '12:00', 15),
(29, '2025-01-10', '2025-01-20', 100.00, '15:00', '11:00', 1);

BEGIN;
DO $$
DECLARE new_booking_id INT;
BEGIN
    INSERT INTO bookings (id, check_in_date, check_out_date, num_guests, status, total_price, availability_id, guest_id, created_at)
    VALUES (120, '2025-01-11', '2025-01-12', 2, 'Completed', 200.00, 29, 3, '2025-01-10 09:00:00'),
        (121, '2025-01-13', '2025-01-15', 2, 'Completed', 200.00, 29, 3, '2025-01-12 09:00:00');

    INSERT INTO transactions (amount, transaction_method, type, status, paid_at, booking_id)
    VALUES (200.00, 'Card', 'FullPayment', 'Completed', '2025-01-11 10:00:00', 120),
        (200.00, 'Card', 'FullPayment', 'Completed', '2025-01-14 10:00:00', 121);

    INSERT INTO bookings (id, check_in_date, check_out_date, num_guests, status, total_price, availability_id, guest_id, created_at)
    VALUES (122, '2025-01-16', '2025-01-18', 2, 'Completed', 200.00, 29, 3, '2025-01-15 09:00:00')
    RETURNING id INTO new_booking_id;

    INSERT INTO transactions (amount, transaction_method, type, status, paid_at, booking_id)
    VALUES (200.00, 'Card', 'FullPayment', 'Completed', '2025-01-17 10:00:00', new_booking_id);
END $$;
COMMIT;

BEGIN;
DO $$
DECLARE b_id INT;
BEGIN
    FOR i IN 9..19 LOOP
        INSERT INTO bookings (id, check_in_date, check_out_date, num_guests, status, total_price, availability_id, guest_id)
        VALUES (i + 100, (SELECT start_date FROM availability WHERE id = i), (SELECT end_date FROM availability WHERE id = i), 2, 'Completed', (SELECT price_per_night * 2 FROM availability WHERE id = i), i, CASE WHEN i % 3 = 0 THEN 3 WHEN i % 3 = 1 THEN 4 ELSE 5 END)
        RETURNING id INTO b_id;

        INSERT INTO transactions (amount, transaction_method, type, status, paid_at, booking_id)
        VALUES ((SELECT total_price FROM bookings WHERE id = b_id), 'Card', 'FullPayment', 'Completed', (SELECT CURRENT_TIMESTAMP), b_id);
    END LOOP;

    INSERT INTO bookings (id, check_in_date, check_out_date, num_guests, status, total_price, availability_id, guest_id)
    VALUES (30, '2026-02-10', '2026-02-14', 3, 'Confirmed', 640.00, 19, 12),
           (31, '2026-03-09', '2026-03-13', 2, 'Confirmed', 660.00, 21, 13),
           (32, '2026-03-11', '2026-03-15', 2, 'Confirmed', 720.00, 22, 15);

    INSERT INTO transactions (amount, transaction_method, type, status, paid_at, booking_id)
    VALUES (640.00, 'Paypal', 'FullPayment', 'Completed', CURRENT_TIMESTAMP, 30),
           (660.00, 'Card', 'FullPayment', 'Completed', CURRENT_TIMESTAMP, 31),
           (720.00, 'Card', 'FullPayment', 'Completed', CURRENT_TIMESTAMP, 32);
END $$;
COMMIT;

BEGIN;
DO $$
DECLARE r_bid INT;
DECLARE idx INT := 9;
BEGIN
    FOR idx IN 9..19 LOOP
        SELECT id INTO r_bid FROM bookings WHERE availability_id = idx LIMIT 1;
        IF r_bid IS NOT NULL THEN
            INSERT INTO reviews (id, rating, comment, guest_id, host_id, booking_id, created_at)
            VALUES (idx + 100, 4 + (idx % 2), 'Pleasant stay, well-maintained property and helpful host.', (CASE WHEN idx % 3 = 0 THEN 3 WHEN idx % 3 = 1 THEN 4 ELSE 5 END), 11, r_bid, (SELECT (end_date + INTERVAL '1 day') FROM availability WHERE id = idx));
        END IF;
    END LOOP;
END $$;
COMMIT;


BEGIN;
DO $$
DECLARE new_booking_id INT;
BEGIN
    INSERT INTO bookings (id, check_in_date, check_out_date, num_guests, status, total_price, availability_id, guest_id)
    VALUES (1, '2025-12-05', '2025-12-10', 2, 'PendingPayment', 600.00, 1, 3) 
    RETURNING id INTO new_booking_id; 

    INSERT INTO transactions (amount, transaction_method, type, status, booking_id)
    VALUES (600.00, 'Card', 'FullPayment', 'Pending', new_booking_id);
END $$;
COMMIT;

INSERT INTO tags (name) VALUES
('house'),
('apartment'),
('hotel'),
('villa'),
('cabin'),
('loft'),
('beachfront'),
('family-friendly'),
('pet-friendly'),
('studio');

INSERT INTO listing_tags (tag_id, listing_id) VALUES
((SELECT id FROM tags WHERE name='apartment'), 1),
((SELECT id FROM tags WHERE name='studio'), 1),
((SELECT id FROM tags WHERE name='family-friendly'), 1),
((SELECT id FROM tags WHERE name='villa'), 2),
((SELECT id FROM tags WHERE name='beachfront'), 2),
((SELECT id FROM tags WHERE name='family-friendly'), 2),
((SELECT id FROM tags WHERE name='pet-friendly'), 2),
((SELECT id FROM tags WHERE name='cabin'), 3),
((SELECT id FROM tags WHERE name='pet-friendly'), 3),
((SELECT id FROM tags WHERE name='loft'), 4),
((SELECT id FROM tags WHERE name='apartment'), 4),
((SELECT id FROM tags WHERE name='house'), 5),
((SELECT id FROM tags WHERE name='family-friendly'), 5),
((SELECT id FROM tags WHERE name='beachfront'), 6),
((SELECT id FROM tags WHERE name='villa'), 6),
((SELECT id FROM tags WHERE name='family-friendly'), 6),
((SELECT id FROM tags WHERE name='cabin'), 7),
((SELECT id FROM tags WHERE name='family-friendly'), 7),
((SELECT id FROM tags WHERE name='house'), 8),
((SELECT id FROM tags WHERE name='apartment'), 9),
((SELECT id FROM tags WHERE name='studio'), 9),
((SELECT id FROM tags WHERE name='loft'), 13),
((SELECT id FROM tags WHERE name='apartment'), 10);


BEGIN;
DO $$
DECLARE new_booking_id INT;
BEGIN
    INSERT INTO bookings (id, check_in_date, check_out_date, num_guests, status, total_price, availability_id, guest_id)
    VALUES (2, '2025-12-11', '2025-12-16', 6, 'PendingPayment', 1750.00, 3, 4) 
    RETURNING id INTO new_booking_id; 

    INSERT INTO transactions (amount, transaction_method, type, status, booking_id)
    VALUES (1750.00, 'Paypal', 'FullPayment', 'Pending', new_booking_id);
END $$;
COMMIT;

BEGIN;
DO $$
DECLARE new_booking_id INT;
BEGIN
    INSERT INTO bookings (id, check_in_date, check_out_date, num_guests, status, total_price, availability_id, guest_id)
    VALUES (3, '2025-12-20', '2025-12-23', 4, 'PendingPayment', 270.00, 5, 5)
    RETURNING id INTO new_booking_id; 

    INSERT INTO transactions (amount, transaction_method, type, status, booking_id)
    VALUES (270.00, 'Card', 'FullPayment', 'Pending', new_booking_id);
END $$;
COMMIT;

BEGIN;
DO $$
DECLARE new_booking_id INT;
BEGIN
    INSERT INTO bookings (id, check_in_date, check_out_date, num_guests, status, total_price, availability_id, guest_id)
    VALUES (4, '2025-12-15', '2025-12-18', 2, 'PendingPayment', 540.00, 7, 3)
    RETURNING id INTO new_booking_id; 

    INSERT INTO transactions (amount, transaction_method, type, status, booking_id)
    VALUES (540.00, 'Bank Transfer', 'FullPayment', 'Pending', new_booking_id);
END $$;
COMMIT;

BEGIN;
DO $$
DECLARE new_booking_id INT;
BEGIN
    INSERT INTO bookings (id, check_in_date, check_out_date, num_guests, status, total_price, availability_id, guest_id)
    VALUES (5, '2026-01-05', '2026-01-10', 5, 'PendingPayment', 1050.00, 8, 4) 
    RETURNING id INTO new_booking_id; 

    INSERT INTO transactions (amount, transaction_method, type, status, booking_id)
    VALUES (1050.00, 'Card', 'FullPayment', 'Pending', new_booking_id);
END $$;
COMMIT;

UPDATE bookings SET status = 'Confirmed', created_at = '2025-01-20 09:59:00' WHERE id = 1; 
UPDATE transactions SET status = 'Completed', paid_at = '2025-01-20 10:00:00' WHERE booking_id = 1;

UPDATE bookings SET status = 'Confirmed', created_at = '2025-01-22 10:59:00' WHERE id = 2;
UPDATE transactions SET status = 'Completed', paid_at = '2025-01-22 11:00:00' WHERE booking_id = 2;

UPDATE bookings SET status = 'Confirmed', created_at = '2025-01-25 11:59:00' WHERE id = 3;
UPDATE transactions SET status = 'Completed', paid_at = '2025-01-25 12:00:00' WHERE booking_id = 3;

UPDATE bookings SET status = 'Rejected' WHERE id = 4; 
UPDATE transactions SET status = 'Failed' WHERE booking_id = 4;

UPDATE bookings SET status = 'Confirmed', created_at = '2025-01-28 13:59:00' WHERE id = 5;
UPDATE transactions SET status = 'Completed', paid_at = '2025-01-28 14:00:00' WHERE booking_id = 5;

BEGIN;
INSERT INTO reviews (id, rating, comment, guest_id, host_id, booking_id, created_at)
VALUES (
    1,
    5, 
    'Absolutely perfect location! David was a great host. Highly recommend.', 
    3, 
    6, 
    120, 
    '2025-01-13 12:00:00' 
);
COMMIT;

BEGIN;
INSERT INTO reviews (id, rating, comment, guest_id, host_id, booking_id, created_at)
VALUES (
    2,
    4, 
    'The view was amazing, but the Wi-Fi was a bit slow. Overall a good stay.', 
    3, 
    6, 
    121, 
    '2025-01-16 11:00:00' 
);
COMMIT;

BEGIN;
INSERT INTO conversations (id, guest_id, host_id, listing_id) VALUES (1, 5, 6, 1);
INSERT INTO messages (author, content, conversation_id) VALUES
('Guest', 'Hi David, is the apartment pet-friendly?', 1),
('Host', 'Hi Charlie, unfortunately, we do not allow pets.', 1),
('Guest', 'No problem, thanks for the quick reply!', 1);

INSERT INTO conversations (id, guest_id, host_id, booking_id) VALUES (2, 5, 8, 3);
INSERT INTO messages (author, content, conversation_id) VALUES
('Guest', 'Hi Frank, we are excited for our stay! Just wondering if the cabin has a coffee maker?', 2),
('Host', 'Hello! Yes, it has a standard drip coffee maker with filters provided.', 2);

INSERT INTO conversations (id, guest_id, host_id, listing_id) VALUES (3, 4, 9, 4);
INSERT INTO messages (author, content, conversation_id) VALUES
('Guest', 'Is the Wi-Fi fast? I need to work remotely.', 3);

COMMIT;

INSERT INTO notifications (type, content, user_id, booking_id)
VALUES ('BookingConfirmed', 'Your booking for Cozy Downtown Apartment is confirmed!', 3, 1); 

INSERT INTO notifications (type, content, user_id, booking_id)
VALUES ('BookingCanceled', 'Your booking for Quiet Suburban House has been cancelled.', 4, 5); 

INSERT INTO notifications (type, content, user_id, review_id)
VALUES ('ReviewLeft', 'Alice G. left a 5-star review for your listing!', 6, 1); 

INSERT INTO notifications (type, content, user_id, admin_id, admin_notification_id)
VALUES ('AdminNotice', 'A new host application from Heidi H. is ready for review.', 1, 1, 1); 

SELECT setval('users_id_seq', (SELECT MAX(id) FROM users));
SELECT setval('admin_notification_id_seq', (SELECT MAX(id) FROM admin_notification));
SELECT setval('listings_id_seq', (SELECT MAX(id) FROM listings));
SELECT setval('availability_id_seq', (SELECT MAX(id) FROM availability));
SELECT setval('listing_photos_id_seq', (SELECT MAX(id) FROM listing_photos));
SELECT setval('bookings_id_seq', (SELECT MAX(id) FROM bookings));
SELECT setval('transactions_id_seq', (SELECT MAX(id) FROM transactions));
SELECT setval('reviews_id_seq', (SELECT MAX(id) FROM reviews));
SELECT setval('conversations_id_seq', (SELECT MAX(id) FROM conversations));
SELECT setval('messages_id_seq', (SELECT MAX(id) FROM messages));
SELECT setval('notifications_id_seq', (SELECT MAX(id) FROM notifications));
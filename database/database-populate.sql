SET search_path TO lbaw2581;

INSERT INTO admin_notification (type) VALUES
('NewHostApplication'),
('ListingReported'),
('UserSuspended');

INSERT INTO users (id, full_name, email, password_hash, date_of_birth, phone, is_admin) VALUES
(1, 'Admin Staff', 'admin@stay.com', '$2a$10$E.qfVFfN3B.sW/KocB.kReFYv/NnS8SBEsFl5.sLBS.SGOBCm.iTK', '1990-01-01', '+111222333', true),
(2, 'Super Admin', 'super@stay.com', '$2a$10$E.qfVFfN3B.sW/KocB.kReFYv/NnS8SBEsFl5.sLBS.SGOBCm.iTK', '1985-05-10', '+144555666', true);


INSERT INTO users (id, full_name, email, password_hash, date_of_birth, phone) VALUES
(3, 'Alice Green', 'alice@guest.com', '$2a$10$E.qfVFfN3B.sW/KocB.kReFYv/NnS8SBEsFl5.sLBS.SGOBCm.iTK', '1995-04-12', '+111111111'),
(4, 'Bob White', 'bob@guest.com', '$2a$10$E.qfVFfN3B.sW/KocB.kReFYv/NnS8SBEsFl5.sLBS.SGOBCm.iTK', '2000-07-20', '+222222222'),
(5, 'Charlie Black', 'charlie@guest.com', '$2a$10$E.qfVFfN3B.sW/KocB.kReFYv/NnS8SBEsFl5.sLBS.SGOBCm.iTK', '1998-11-30', '+333333333');

INSERT INTO users (id, full_name, email, password_hash, date_of_birth, phone, application_status, answers, verification_docs, registration_date, submitted_at) VALUES
(6, 'David Host', 'david@host.com', '$2a$10$E.qfVFfN3B.sW/KocB.kReFYv/NnS8SBEsFl5.sLBS.SGOBCm.iTK', '1992-02-15', '+444444444', 'Pending', 'My answers to the questions.', 'path/to/docs_david.pdf', '2025-01-09 09:00:00', '2025-01-10 09:00:00'),
(7, 'Eve Owner', 'eve@host.com', '$2a$10$E.qfVFfN3B.sW/KocB.kReFYv/NnS8SBEsFl5.sLBS.SGOBCm.iTK', '1988-06-25', '+555555555', 'Pending', 'I have a great property.', 'path/to/docs_eve.pdf', '2025-01-10 10:00:00', '2025-01-11 10:00:00'),
(8, 'Frank Landlord', 'frank@host.com', '$2a$10$E.qfVFfN3B.sW/KocB.kReFYv/NnS8SBEsFl5.sLBS.SGOBCm.iTK', '1990-09-05', '+666666666', 'Pending', 'Here are my details.', 'path/to/docs_frank.pdf', '2025-01-11 11:00:00', '2025-01-12 11:00:00'),
(9, 'Grace Property', 'grace@host.com', '$2a$10$E.qfVFfN3B.sW/KocB.kReFYv/NnS8SBEsFl5.sLBS.SGOBCm.iTK', '1993-01-18', '+777777777', 'Pending', 'My answers.', 'path/to/docs_grace.pdf', '2025-01-12 12:00:00', '2025-01-13 12:00:00'),
(10, 'Heidi Home', 'heidi@host.com', '$2a$10$E.qfVFfN3B.sW/KocB.kReFYv/NnS8SBEsFl5.sLBS.SGOBCm.iTK', '1985-03-22', '+888888888', 'Pending', 'Application answers.', 'path/to/docs_heidi.pdf', '2025-01-13 13:00:00', '2025-01-14 13:00:00');

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
    INSERT INTO listings (id, title, description, max_occupancy, location_address, minimum_stay, cancellation_policy, host_id)
    VALUES (1, 'Cozy Downtown Apartment', 'A beautiful apartment in the heart of the city.', 2, '123 Main St, Cityville', 2, 'Flexible: Full refund 1 day prior to arrival.', 6)
    RETURNING id INTO new_listing_id; 

    INSERT INTO listing_photos (url, listing_id) VALUES ('https://example.com/photo/1_1.jpg', new_listing_id);
    INSERT INTO listing_photos (url, listing_id) VALUES ('https://example.com/photo/1_2.jpg', new_listing_id); 
    INSERT INTO listing_photos (url, listing_id) VALUES ('https://example.com/photo/1_3.jpg', new_listing_id); 
END $$;
COMMIT;

BEGIN;
DO $$
DECLARE
    new_listing_id INT;
BEGIN
    INSERT INTO listings (id, title, description, max_occupancy, location_address, minimum_stay, cancellation_policy, host_id)
    VALUES (2, 'Sunny Beachside Villa', 'Stunning views of the ocean, perfect for a getaway.', 8, '456 Ocean Ave, Beachtown', 5, 'Strict: 50% refund up to 1 week prior to arrival.', 7)
    RETURNING id INTO new_listing_id; 

    INSERT INTO listing_photos (url, listing_id) VALUES ('https://example.com/photo/2_1.jpg', new_listing_id);
    INSERT INTO listing_photos (url, listing_id) VALUES ('https://example.com/photo/2_2.jpg', new_listing_id); 
    INSERT INTO listing_photos (url, listing_id) VALUES ('https://example.com/photo/2_3.jpg', new_listing_id); 
    INSERT INTO listing_photos (url, listing_id) VALUES ('https://example.com/photo/2_4.jpg', new_listing_id); -- More than 3 is fine
END $$;
COMMIT;

BEGIN;
DO $$
DECLARE
    new_listing_id INT;
BEGIN
    INSERT INTO listings (id, title, description, max_occupancy, location_address, minimum_stay, cancellation_policy, host_id)
    VALUES (3, 'Rustic Mountain Cabin', 'Escape to the wilderness. No Wi-Fi, pure nature.', 4, '789 Pine Rd, Mountainside', 3, 'Moderate: Full refund 5 days prior to arrival.', 8)
    RETURNING id INTO new_listing_id; 

    INSERT INTO listing_photos (url, listing_id) VALUES ('https://example.com/photo/3_1.jpg', new_listing_id);
    INSERT INTO listing_photos (url, listing_id) VALUES ('https://example.com/photo/3_2.jpg', new_listing_id); 
    INSERT INTO listing_photos (url, listing_id) VALUES ('https://example.com/photo/3_3.jpg', new_listing_id); 
END $$;
COMMIT;

BEGIN;
DO $$
DECLARE
    new_listing_id INT;
BEGIN
    INSERT INTO listings (id, title, description, max_occupancy, location_address, minimum_stay, cancellation_policy, host_id)
    VALUES (4, 'Modern City Loft', 'A stylish loft with all modern amenities.', 3, '321 Urban Way, Metropolis', 2, 'Flexible: Full refund 1 day prior to arrival.', 9)
    RETURNING id INTO new_listing_id; 

    INSERT INTO listing_photos (url, listing_id) VALUES ('https://example.com/photo/4_1.jpg', new_listing_id);
    INSERT INTO listing_photos (url, listing_id) VALUES ('https://example.com/photo/4_2.jpg', new_listing_id); 
    INSERT INTO listing_photos (url, listing_id) VALUES ('https://example.com/photo/4_3.jpg', new_listing_id); 
END $$;
COMMIT;

BEGIN;
DO $$
DECLARE
    new_listing_id INT;
BEGIN
    INSERT INTO listings (id, title, description, max_occupancy, location_address, minimum_stay, cancellation_policy, host_id)
    VALUES (5, 'Quiet Suburban House', 'Perfect for families. Large backyard and grill.', 6, '654 Suburb Ln, Greendale', 4, 'Moderate: Full refund 5 days prior to arrival.', 10)
    RETURNING id INTO new_listing_id; 

    INSERT INTO listing_photos (url, listing_id) VALUES ('https://example.com/photo/5_1.jpg', new_listing_id);
    INSERT INTO listing_photos (url, listing_id) VALUES ('https://example.com/photo/5_2.jpg', new_listing_id); 
    INSERT INTO listing_photos (url, listing_id) VALUES ('https://example.com/photo/5_3.jpg', new_listing_id); 
END $$;
COMMIT;

INSERT INTO availability VALUES
(1, '2025-02-01', '2025-02-28', 120.00, '15:00', '11:00', 1), 
(2, '2025-03-01', '2025-03-31', 125.00, '15:00', '11:00', 1), 
(3, '2025-02-10', '2025-02-20', 350.00, '16:00', '10:00', 2), 
(4, '2025-03-05', '2025-03-20', 350.00, '16:00', '10:00', 2), 
(5, '2025-02-01', '2025-03-31', 90.00, '14:00', '12:00', 3), 
(6, '2025-04-01', '2025-05-31', 95.00, '14:00', '12:00', 3), 
(7, '2025-02-15', '2025-04-15', 180.00, '15:00', '11:00', 4),
(8, '2025-03-01', '2025-03-31', 210.00, '15:00', '11:00', 5); 

BEGIN;
DO $$
DECLARE new_booking_id INT;
BEGIN
    INSERT INTO bookings (id, check_in_date, check_out_date, num_guests, status, total_price, availability_id, guest_id)
    VALUES (1, '2025-02-05', '2025-02-10', 2, 'PendingPayment', 600.00, 1, 3) 
    RETURNING id INTO new_booking_id; 

    INSERT INTO transactions (amount, transaction_method, type, status, booking_id)
    VALUES (600.00, 'Card', 'FullPayment', 'Pending', new_booking_id);
END $$;
COMMIT;

BEGIN;
DO $$
DECLARE new_booking_id INT;
BEGIN
    INSERT INTO bookings (id, check_in_date, check_out_date, num_guests, status, total_price, availability_id, guest_id)
    VALUES (2, '2025-02-11', '2025-02-16', 6, 'PendingPayment', 1750.00, 3, 4) 
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
    VALUES (3, '2025-02-20', '2025-02-23', 4, 'PendingPayment', 270.00, 5, 5)
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
    VALUES (4, '2025-03-01', '2025-03-04', 2, 'PendingPayment', 540.00, 7, 3)
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
    VALUES (5, '2025-03-05', '2025-03-10', 5, 'PendingPayment', 1050.00, 8, 4) 
    RETURNING id INTO new_booking_id; 

    INSERT INTO transactions (amount, transaction_method, type, status, booking_id)
    VALUES (1050.00, 'Card', 'FullPayment', 'Pending', new_booking_id);
END $$;
COMMIT;

UPDATE bookings SET status = 'Completed', created_at = '2025-01-20 09:59:00' WHERE id = 1; 
UPDATE transactions SET status = 'Completed', paid_at = '2025-01-20 10:00:00' WHERE booking_id = 1;

UPDATE bookings SET status = 'Completed', created_at = '2025-01-22 10:59:00' WHERE id = 2;
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
    1, 
    '2025-02-10 12:00:00' 
);
COMMIT;

BEGIN;
INSERT INTO reviews (id, rating, comment, guest_id, host_id, booking_id, created_at)
VALUES (
    2,
    4, 
    'The view was amazing, but the Wi-Fi was a bit slow. Overall a good stay.', 
    4, 
    7, 
    2, 
    '2025-02-16 11:00:00' 
);
COMMIT;

BEGIN;
DO $$
DECLARE
    original_tx_id INT;
    original_tx_method VARCHAR(20);
BEGIN
    SELECT id, transaction_method
    INTO original_tx_id, original_tx_method
    FROM transactions
    WHERE
        booking_id = 5
        AND type = 'FullPayment'
        AND status = 'Completed'
    FOR UPDATE; 

    IF NOT FOUND THEN
        RAISE EXCEPTION 'No completed payment found for booking %', 5;
    END IF;

    UPDATE bookings
    SET status = 'Cancelled'
    WHERE id = 5;
    
    UPDATE transactions
    SET status = 'Refunded'
    WHERE id = original_tx_id;

    INSERT INTO transactions (amount, transaction_method, type, status, paid_at, refund_id, booking_id)
    VALUES (
        1050.00,
        original_tx_method,
        'Refund',
        'Completed',
        '2025-01-29 14:00:00',
        original_tx_id, 
        5
    );
END $$;
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
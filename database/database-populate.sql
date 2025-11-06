DROP TABLE IF EXISTS users CASCADE;
DROP TABLE IF EXISTS admins CASCADE;
DROP TABLE IF EXISTS host_profiles CASCADE;
DROP TABLE IF EXISTS listings CASCADE;
DROP TABLE IF EXISTS listing_photos CASCADE;
DROP TABLE IF EXISTS blocked_dates CASCADE;
DROP TABLE IF EXISTS conversations CASCADE;
DROP TABLE IF EXISTS messages CASCADE;
DROP TABLE IF EXISTS bookings CASCADE;
DROP TABLE IF EXISTS reviews CASCADE;
DROP TABLE IF EXISTS notifications CASCADE;
DROP TABLE IF EXISTS reviews CASCADE;

-- Populate tables with sample data

INSERT INTO users (full_name, email, password_hash, date_of_birth, phone, profile_picture_url, is_active) 
VALUES 
('Sarah Mitchell', 'sarah.mitchell@email.com', '$2a$10$abcdefghijklmnopqrstuvwxyz123456', '1995-03-15', '+12025551234', 'https://example.com/profiles/sarah.jpg', true),
('James Anderson', 'james.anderson@webmail.org', '$2a$10$xyz789abcdef123456ghijklmnopqrstuv', '1988-07-22', '+442071234567', 'https://example.com/profiles/james.jpg', true),
('Maria Garcia', 'maria.garcia@inbox.es', '$2a$10$qwerty123456asdfgh789zxcvbn012345', '1992-11-08', '+34912345678', NULL, true),
('David Chen', 'david.chen@techmail.com', '$2a$10$mnbvcx987654poiuyt321098lkjhgfdsa', '1990-05-30', '+8613812345678', 'https://example.com/profiles/david.jpg', false),
('Emma Thompson', 'emma.thompson@postbox.uk', '$2a$10$zaqwsx123edc456rfv789tgb098yhn765', '1985-12-03', '+447911123456', 'https://example.com/profiles/emma.jpg', true),
('Mohammed Al-Rashid', 'mohammed.alrashid@mail.ae', '$2a$10$plmoknijbuhvygctfxrdzeswaq147258', '1993-09-17', '+971501234567', 'https://example.com/profiles/mohammed.jpg', true),
('Sophie Dubois', 'sophie.dubois@courier.fr', '$2a$10$qazxswedc147rfvtgb258369yhnujm0ik', '1996-02-28', '+33612345678', NULL, false),
('Lucas Silva', 'lucas.silva@correio.br', '$2a$10$okmlpnhybgtfrvcdeswxza159753486207', '1991-06-12', '+5511987654321', 'https://example.com/profiles/lucas.jpg', true),
('Aisha Okafor', 'aisha.okafor@netmail.ng', '$2a$10$mnbgtrfvcdexswzaq147258369qpolkmji', '1994-04-25', '+2348012345678', 'https://example.com/profiles/aisha.jpg', true),
('Oliver Schmidt', 'oliver.schmidt@webpost.de', '$2a$10$lkjhgfdsazxcvbnm098poiuytrewq12345', '1987-10-19', '+4917012345678', NULL, true),
('Priya Patel', 'priya.patel@connect.in', '$2a$10$qwertyasdfghzxcvbn147258369plmokniju', '1998-01-07', '+919876543210', 'https://example.com/profiles/priya.jpg', false),
('Carlos Rodriguez', 'carlos.rodriguez@mensaje.mx', '$2a$10$zxcvbnmasdfghjklqwertyuiop098765432', '1989-08-14', '+525512345678', 'https://example.com/profiles/carlos.jpg', true),
('Anna Kowalski', 'anna.kowalski@poczta.pl', '$2a$10$poiuytrewqlkjhgfdsamnbvcxz098123456', '1997-05-21', '+48601234567', NULL, true),
('Yuki Tanaka', 'yuki.tanaka@mailbox.jp', '$2a$10$asdfghjklzxcvbnmqwertyuiop147852369', '1986-03-09', '+819012345678', 'https://example.com/profiles/yuki.jpg', true),
('Isabella Rossi', 'isabella.rossi@posta.it', '$2a$10$mnbvcxzlkjhgfdsapoiuytrewq987654321', '1995-11-26', '+393401234567', 'https://example.com/profiles/isabella.jpg', false);

INSERT INTO admins (full_name, email, password_hash) 
VALUES 
('Michael Brooks', 'michael.brooks@admin.com', '$2a$10$adminHash001234567890abcdefghijklmn'),
('Jennifer Martinez', 'jennifer.martinez@admin.com', '$2a$10$adminHash098765432109876543210zyxwvu'),
('Robert Kim', 'robert.kim@admin.com', '$2a$10$adminHash112233445566778899aabbccddee'),
('Laura Fernandez', 'laura.fernandez@admin.com', '$2a$10$adminHash223344556677889900ffeeddccbb'),
('Thomas Wagner', 'thomas.wagner@admin.com', '$2a$10$adminHash334455667788990011gghhiijjkk'),
('Rachel Green', 'rachel.green@admin.com', '$2a$10$adminHash445566778899001122llmmnnoopp'),
('Ahmed Hassan', 'ahmed.hassan@admin.com', '$2a$10$adminHash556677889900112233qqrrssttuuvv'),
('Catherine Blanc', 'catherine.blanc@admin.com', '$2a$10$adminHash667788990011223344wwxxyyzz00'),
('Daniel Santos', 'daniel.santos@admin.com', '$2a$10$adminHash778899001122334455aa11bb22cc'),
('Victoria Novak', 'victoria.novak@admin.com', '$2a$10$adminHash889900112233445566dd33ee44ff'),
('Kevin O Brien', 'kevin.obrien@admin.com', '$2a$10$adminHash990011223344556677gg55hh66ii'),
('Fatima Al-Zahra', 'fatima.alzahra@admin.com', '$2a$10$adminHash001122334455667788jj77kk88ll'),
('Marcus Johansson', 'marcus.johansson@admin.com', '$2a$10$adminHash112233445566778899mm99nn00oo'),
('Nina Petrov', 'nina.petrov@admin.com', '$2a$10$adminHash223344556677889900pp11qq22rr'),
('Andrew Taylor', 'andrew.taylor@admin.com', '$2a$10$adminHash334455667788990011ss33tt44uu');

INSERT INTO host_profiles (answers, verification_docs, application_status, is_verified, payout_details, submited_at, verified_at, user_id, verifier_id) 
VALUES 
('I have 5 years of hosting experience. I own a 3-bedroom apartment in downtown with modern amenities.', 'https://docs.example.com/verify/doc001.pdf', 'Approved', true, '{"bank": "Chase", "account": "****1234", "routing": "021000021"}', CURRENT_TIMESTAMP + INTERVAL '2 days', CURRENT_TIMESTAMP + INTERVAL '5 days', 1, 1),
('I want to share my vacation home with travelers. Property is located near the beach with parking.', 'https://docs.example.com/verify/doc002.pdf', 'Approved', true, '{"bank": "Bank of America", "account": "****5678", "routing": "026009593"}', CURRENT_TIMESTAMP + INTERVAL '3 days', CURRENT_TIMESTAMP + INTERVAL '6 days', 2, 2),
('First time host, excited to welcome guests to my cozy studio apartment in the city center.', 'https://docs.example.com/verify/doc003.pdf', 'Pending', false, NULL, CURRENT_TIMESTAMP + INTERVAL '1 day', NULL, 3, NULL),
('Experienced property manager with multiple listings. Looking to add another property to my portfolio.', 'https://docs.example.com/verify/doc004.pdf', 'Approved', true, '{"bank": "Wells Fargo", "account": "****9012", "routing": "121000248"}', CURRENT_TIMESTAMP + INTERVAL '4 days', CURRENT_TIMESTAMP + INTERVAL '7 days', 4, 3),
('I have a guest house in my backyard that I would like to rent out to travelers.', 'https://docs.example.com/verify/doc005.pdf', 'Rejected', false, NULL, CURRENT_TIMESTAMP + INTERVAL '5 days', NULL, 5, 4),
('My family home has extra rooms available. We love hosting international guests and sharing our culture.', 'https://docs.example.com/verify/doc006.pdf', 'Approved', true, '{"bank": "Citibank", "account": "****3456", "routing": "021000089"}', CURRENT_TIMESTAMP + INTERVAL '2 days', CURRENT_TIMESTAMP + INTERVAL '4 days', 6, 5),
('Luxury villa with pool and garden. Professional hosting service with 24/7 support.', 'https://docs.example.com/verify/doc007.pdf', 'Pending', false, NULL, CURRENT_TIMESTAMP + INTERVAL '3 days', NULL, 7, NULL),
('Charming cottage in the countryside, perfect for weekend getaways. Pet-friendly property.', 'https://docs.example.com/verify/doc008.pdf', 'Approved', true, '{"bank": "US Bank", "account": "****7890", "routing": "091000022"}', CURRENT_TIMESTAMP + INTERVAL '6 days', CURRENT_TIMESTAMP + INTERVAL '9 days', 8, 1),
('Modern loft in artistic neighborhood. Close to galleries, restaurants, and public transport.', 'https://docs.example.com/verify/doc009.pdf', 'Approved', true, '{"bank": "PNC Bank", "account": "****2345", "routing": "043000096"}', CURRENT_TIMESTAMP + INTERVAL '1 day', CURRENT_TIMESTAMP + INTERVAL '3 days', 9, 2),
('Traditional house with historical significance. Interested in cultural exchange with guests.', 'https://docs.example.com/verify/doc010.pdf', 'Rejected', false, NULL, CURRENT_TIMESTAMP + INTERVAL '4 days', NULL, 10, 6),
('Beachfront property with stunning ocean views. Fully equipped kitchen and outdoor BBQ area.', 'https://docs.example.com/verify/doc011.pdf', 'Approved', true, '{"bank": "TD Bank", "account": "****6789", "routing": "031201360"}', CURRENT_TIMESTAMP + INTERVAL '2 days', CURRENT_TIMESTAMP + INTERVAL '5 days', 11, 3),
('Cozy mountain cabin ideal for nature lovers. Hiking trails nearby and fireplace inside.', 'https://docs.example.com/verify/doc012.pdf', 'Pending', false, NULL, CURRENT_TIMESTAMP + INTERVAL '7 days', NULL, 12, NULL),
('Downtown penthouse with city skyline views. High-end amenities and concierge service.', 'https://docs.example.com/verify/doc013.pdf', 'Approved', true, '{"bank": "Capital One", "account": "****4567", "routing": "065000090"}', CURRENT_TIMESTAMP + INTERVAL '3 days', CURRENT_TIMESTAMP + INTERVAL '6 days', 13, 4),
('Family-friendly home with backyard playground. Located in safe neighborhood near schools.', 'https://docs.example.com/verify/doc014.pdf', 'Approved', true, '{"bank": "HSBC", "account": "****8901", "routing": "022000020"}', CURRENT_TIMESTAMP + INTERVAL '5 days', CURRENT_TIMESTAMP + INTERVAL '8 days', 14, 5),
('Eco-friendly tiny house off the grid. Solar powered with composting toilet and rainwater collection.', 'https://docs.example.com/verify/doc015.pdf', 'Pending', false, NULL, CURRENT_TIMESTAMP + INTERVAL '1 day', NULL, 15, NULL);

INSERT INTO listings (title, description, price_per_night, max_occupancy, location_address, created_at, is_active, minimum_stay, cancellation_policy, security_deposit_amount, check_in_time, check_out_time, average_rating, host_id) 
VALUES 
('Modern Downtown Apartment', 'Spacious 3-bedroom apartment in the heart of downtown. Walking distance to restaurants, shopping, and entertainment. Features modern amenities including high-speed WiFi, smart TV, and fully equipped kitchen.', 150.00, 6, '123 Main Street, Apt 5B, New York, NY 10001', CURRENT_TIMESTAMP + INTERVAL '3 days', true, 2, 'Free cancellation up to 7 days before check-in. 50% refund for cancellations 3-7 days before. No refund within 3 days of check-in.', 200.00, '15:00:00', '11:00:00', 4.85, 1),
('Beachfront Paradise Villa', 'Stunning beachfront property with private beach access. Includes outdoor pool, BBQ area, and panoramic ocean views. Perfect for families and groups looking for a luxurious beach vacation.', 320.00, 8, '456 Ocean Drive, Malibu, CA 90265', CURRENT_TIMESTAMP + INTERVAL '4 days', true, 3, 'Moderate cancellation policy. Full refund if cancelled 14+ days before check-in. 50% refund for 7-14 days before. No refund within 7 days.', 500.00, '16:00:00', '10:00:00', 4.92, 2),
('Cozy City Studio', 'Charming studio apartment perfect for solo travelers or couples. Recently renovated with comfortable furnishings and all essentials. Great location near public transportation.', 75.00, 2, '789 Park Avenue, Apt 12C, Boston, MA 02108', CURRENT_TIMESTAMP + INTERVAL '2 days', true, 1, 'Flexible cancellation policy. Free cancellation up to 24 hours before check-in. No refund for same-day cancellations.', 100.00, '14:00:00', '11:00:00', 4.65, 3),
('Luxury Mountain Retreat', 'Exclusive mountain property with breathtaking views. Features include home theater, wine cellar, hot tub, and concierge service. Ideal for special occasions and corporate retreats.', 450.00, 10, '321 Mountain Ridge Road, Aspen, CO 81611', CURRENT_TIMESTAMP + INTERVAL '5 days', true, 5, 'Strict cancellation policy. 50% refund if cancelled 30+ days before check-in. 25% refund for 14-30 days before. No refund within 14 days.', 1000.00, '16:00:00', '10:00:00', 4.95, 4),
('Historic Downtown Loft', 'Beautiful loft in a renovated historic building. High ceilings, exposed brick walls, and large windows. Walking distance to museums, theaters, and fine dining.', 180.00, 4, '567 Heritage Street, Unit 3A, Philadelphia, PA 19103', CURRENT_TIMESTAMP + INTERVAL '6 days', false, 2, 'Standard cancellation policy. Full refund for cancellations 10+ days before check-in. 50% refund within 5-10 days. No refund within 5 days.', 250.00, '15:00:00', '11:00:00', 0.00, 5),
('Family-Friendly Suburban Home', 'Spacious family home in quiet neighborhood. Large backyard with playground, garage parking, and pet-friendly. Close to schools and shopping centers.', 130.00, 7, '890 Maple Lane, Austin, TX 78701', CURRENT_TIMESTAMP + INTERVAL '3 days', true, 2, 'Moderate cancellation policy. Full refund if cancelled 14+ days before check-in. 50% refund for 7-14 days before. No refund within 7 days.', 200.00, '15:00:00', '10:00:00', 4.78, 6),
('Elegant Countryside Villa', 'Luxurious villa surrounded by vineyards and rolling hills. Private pool, tennis court, and gourmet kitchen. Perfect for wine enthusiasts and those seeking tranquility.', 380.00, 9, '234 Vineyard Road, Napa Valley, CA 94558', CURRENT_TIMESTAMP + INTERVAL '4 days', true, 4, 'Strict cancellation policy. 50% refund if cancelled 30+ days before check-in. 25% refund for 14-30 days before. No refund within 14 days.', 750.00, '16:00:00', '11:00:00', 0.00, 7),
('Rustic Country Cottage', 'Charming cottage in peaceful countryside setting. Wood-burning fireplace, garden, and nearby hiking trails. Pet-friendly property ideal for nature lovers.', 95.00, 4, '678 Country Lane, Blue Ridge, GA 30513', CURRENT_TIMESTAMP + INTERVAL '7 days', true, 2, 'Flexible cancellation policy. Free cancellation up to 3 days before check-in. 50% refund within 1-3 days. No refund for same-day cancellations.', 150.00, '14:00:00', '10:00:00', 4.88, 8),
('Urban Artist Loft', 'Creative space in vibrant arts district. Open floor plan with natural light, perfect for artists and creative professionals. Walking distance to galleries and cafes.', 165.00, 3, '901 Gallery Street, Portland, OR 97209', CURRENT_TIMESTAMP + INTERVAL '2 days', true, 1, 'Standard cancellation policy. Full refund for cancellations 10+ days before check-in. 50% refund within 5-10 days. No refund within 5 days.', 200.00, '15:00:00', '12:00:00', 4.71, 9),
('Traditional Heritage House', 'Beautifully preserved historic home with period furnishings. Offers cultural immersion experience with guided tours available. Perfect for history enthusiasts.', 140.00, 5, '345 Colonial Drive, Charleston, SC 29401', CURRENT_TIMESTAMP + INTERVAL '5 days', true, 3, 'Moderate cancellation policy. Full refund if cancelled 14+ days before check-in. 50% refund for 7-14 days before. No refund within 7 days.', 300.00, '15:00:00', '11:00:00', 0.00, 10),
('Tropical Beach Bungalow', 'Oceanfront bungalow with direct beach access. Outdoor shower, hammocks, and stunning sunset views. Water sports equipment included for guests.', 210.00, 6, '123 Coastal Highway, Key West, FL 33040', CURRENT_TIMESTAMP + INTERVAL '3 days', true, 3, 'Standard cancellation policy. Full refund for cancellations 10+ days before check-in. 50% refund within 5-10 days. No refund within 5 days.', 350.00, '16:00:00', '10:00:00', 4.90, 11),
('Mountain Cabin Escape', 'Secluded cabin nestled in the mountains. Wood stove, outdoor fire pit, and panoramic mountain views. Ideal for digital detox and reconnecting with nature.', 110.00, 4, '789 Pine Forest Trail, Gatlinburg, TN 37738', CURRENT_TIMESTAMP + INTERVAL '8 days', true, 2, 'Flexible cancellation policy. Free cancellation up to 5 days before check-in. 50% refund within 2-5 days. No refund within 2 days.', NULL, '14:00:00', '11:00:00', 0.00, 12),
('Skyline Penthouse Suite', 'Luxury penthouse with 360-degree city views. Rooftop terrace, designer furnishings, and premium amenities. Concierge service and valet parking included.', 550.00, 8, '456 Tower Plaza, Penthouse A, Miami, FL 33131', CURRENT_TIMESTAMP + INTERVAL '4 days', true, 3, 'Strict cancellation policy. 50% refund if cancelled 30+ days before check-in. 25% refund for 14-30 days before. No refund within 14 days.', 1200.00, '16:00:00', '11:00:00', 4.97, 13),
('Garden View Family Home', 'Comfortable home with beautiful garden and outdoor play area. Safe neighborhood, close to parks and family attractions. Board games and toys provided.', 125.00, 6, '234 Garden Way, San Diego, CA 92101', CURRENT_TIMESTAMP + INTERVAL '6 days', true, 2, 'Standard cancellation policy. Full refund for cancellations 10+ days before check-in. 50% refund within 5-10 days. No refund within 5 days.', 200.00, '15:00:00', '10:00:00', 4.82, 14),
('Eco-Friendly Tiny House', 'Off-grid tiny house powered by solar energy. Composting toilet, rainwater collection, and organic garden. Unique sustainable living experience.', 85.00, 2, '567 Green Valley Road, Boulder, CO 80302', CURRENT_TIMESTAMP + INTERVAL '2 days', true, 1, 'Flexible cancellation policy. Free cancellation up to 48 hours before check-in. 50% refund within 24-48 hours. No refund for same-day cancellations.', NULL, '14:00:00', '10:00:00', 0.00, 15);

INSERT INTO listing_photos (url, caption, uploaded_at, listing_id) 
VALUES 
('https://images.example.com/listings/downtown-apt-001.jpg', 'Spacious living room with modern furniture', CURRENT_TIMESTAMP + INTERVAL '3 days 2 hours', 1),
('https://images.example.com/listings/downtown-apt-002.jpg', 'Fully equipped kitchen with stainless steel appliances', CURRENT_TIMESTAMP + INTERVAL '3 days 3 hours', 1),
('https://images.example.com/listings/beachfront-villa-001.jpg', 'Stunning ocean view from master bedroom', CURRENT_TIMESTAMP + INTERVAL '4 days 1 hour', 2),
('https://images.example.com/listings/beachfront-villa-002.jpg', 'Private pool overlooking the beach', CURRENT_TIMESTAMP + INTERVAL '4 days 2 hours', 2),
('https://images.example.com/listings/city-studio-001.jpg', 'Cozy studio with queen bed and work desk', CURRENT_TIMESTAMP + INTERVAL '2 days 1 hour', 3),
('https://images.example.com/listings/mountain-retreat-001.jpg', 'Grand entrance with mountain panorama', CURRENT_TIMESTAMP + INTERVAL '5 days 3 hours', 4),
('https://images.example.com/listings/mountain-retreat-002.jpg', 'Luxurious master suite with fireplace', CURRENT_TIMESTAMP + INTERVAL '5 days 4 hours', 4),
('https://images.example.com/listings/historic-loft-001.jpg', 'Exposed brick walls and high ceilings', CURRENT_TIMESTAMP + INTERVAL '6 days 2 hours', 5),
('https://images.example.com/listings/suburban-home-001.jpg', 'Large backyard with playground equipment', CURRENT_TIMESTAMP + INTERVAL '3 days 1 hour', 6),
('https://images.example.com/listings/countryside-villa-001.jpg', 'Villa surrounded by vineyards at sunset', CURRENT_TIMESTAMP + INTERVAL '4 days 2 hours', 7),
('https://images.example.com/listings/country-cottage-001.jpg', 'Charming cottage exterior with flower garden', CURRENT_TIMESTAMP + INTERVAL '7 days 1 hour', 8),
('https://images.example.com/listings/artist-loft-001.jpg', 'Open floor plan with natural lighting', CURRENT_TIMESTAMP + INTERVAL '2 days 2 hours', 9),
('https://images.example.com/listings/heritage-house-001.jpg', 'Historic facade with colonial architecture', CURRENT_TIMESTAMP + INTERVAL '5 days 1 hour', 10),
('https://images.example.com/listings/beach-bungalow-001.jpg', 'Beachfront view from the deck', CURRENT_TIMESTAMP + INTERVAL '3 days 2 hours', 11),
('https://images.example.com/listings/penthouse-suite-001.jpg', 'Panoramic city skyline from rooftop terrace', CURRENT_TIMESTAMP + INTERVAL '4 days 3 hours', 13);

INSERT INTO blocked_dates (start_date, end_date, notes, listing_id) 
VALUES 
((CURRENT_DATE + INTERVAL '5 days')::DATE, (CURRENT_DATE + INTERVAL '7 days')::DATE, 'Property maintenance and deep cleaning', 1),
((CURRENT_DATE + INTERVAL '15 days')::DATE, (CURRENT_DATE + INTERVAL '20 days')::DATE, 'Owner personal use - family vacation', 2),
((CURRENT_DATE + INTERVAL '3 days')::DATE, (CURRENT_DATE + INTERVAL '3 days')::DATE, 'Plumbing inspection scheduled', 3),
((CURRENT_DATE + INTERVAL '10 days')::DATE, (CURRENT_DATE + INTERVAL '17 days')::DATE, 'Annual winter maintenance', 4),
((CURRENT_DATE + INTERVAL '25 days')::DATE, (CURRENT_DATE + INTERVAL '30 days')::DATE, 'Renovation of guest bathroom', 4),
((CURRENT_DATE + INTERVAL '8 days')::DATE, (CURRENT_DATE + INTERVAL '9 days')::DATE, 'HVAC system upgrade', 5),
((CURRENT_DATE + INTERVAL '12 days')::DATE, (CURRENT_DATE + INTERVAL '14 days')::DATE, 'Family visiting from out of town', 6),
((CURRENT_DATE + INTERVAL '6 days')::DATE, (CURRENT_DATE + INTERVAL '13 days')::DATE, 'Grape harvest season - owner hosting private events', 7),
((CURRENT_DATE + INTERVAL '20 days')::DATE, (CURRENT_DATE + INTERVAL '22 days')::DATE, 'Roof repair scheduled', 8),
((CURRENT_DATE + INTERVAL '4 days')::DATE, (CURRENT_DATE + INTERVAL '5 days')::DATE, 'Professional photography session', 9),
((CURRENT_DATE + INTERVAL '18 days')::DATE, (CURRENT_DATE + INTERVAL '25 days')::DATE, 'Historical society annual meeting', 10),
((CURRENT_DATE + INTERVAL '7 days')::DATE, (CURRENT_DATE + INTERVAL '10 days')::DATE, 'Hurricane season precautions', 11),
((CURRENT_DATE + INTERVAL '30 days')::DATE, (CURRENT_DATE + INTERVAL '35 days')::DATE, 'Winter storm preparation and winterization', 12),
((CURRENT_DATE + INTERVAL '14 days')::DATE, (CURRENT_DATE + INTERVAL '16 days')::DATE, 'Building-wide elevator maintenance', 13),
((CURRENT_DATE + INTERVAL '9 days')::DATE, (CURRENT_DATE + INTERVAL '11 days')::DATE, 'Garden landscaping project', 14);

INSERT INTO bookings (check_in_date, check_out_date, num_guests, status, created_at, security_deposit_held, total_price, security_deposit, listing_id, guest_id) 
VALUES 
((CURRENT_DATE + INTERVAL '5 days')::DATE, (CURRENT_DATE + INTERVAL '8 days')::DATE, 4, 'Confirmed', CURRENT_TIMESTAMP + INTERVAL '3 days 2 hours', NULL, 450.00, 200.00, 1, 5),
((CURRENT_DATE + INTERVAL '10 days')::DATE, (CURRENT_DATE + INTERVAL '13 days')::DATE, 6, 'Completed', CURRENT_TIMESTAMP + INTERVAL '4 days 3 hours', NULL, 960.00, 500.00, 2, 7),
((CURRENT_DATE + INTERVAL '5 days')::DATE, (CURRENT_DATE + INTERVAL '8 days')::DATE, 2, 'Completed', CURRENT_TIMESTAMP + INTERVAL '2 days 1 hour', NULL, 225.00, 100.00, 3, 9),
((CURRENT_DATE + INTERVAL '12 days')::DATE, (CURRENT_DATE + INTERVAL '17 days')::DATE, 8, 'Confirmed', CURRENT_TIMESTAMP + INTERVAL '5 days 2 hours', NULL, 2250.00, 1000.00, 4, 10),
((CURRENT_DATE + INTERVAL '8 days')::DATE, (CURRENT_DATE + INTERVAL '10 days')::DATE, 3, 'Cancelled', CURRENT_TIMESTAMP + INTERVAL '6 days 1 hour', NULL, 360.00, 250.00, 5, 11),
((CURRENT_DATE + INTERVAL '15 days')::DATE, (CURRENT_DATE + INTERVAL '18 days')::DATE, 5, 'Confirmed', CURRENT_TIMESTAMP + INTERVAL '3 days 4 hours', NULL, 390.00, 200.00, 6, 12),
((CURRENT_DATE + INTERVAL '20 days')::DATE, (CURRENT_DATE + INTERVAL '24 days')::DATE, 7, 'PendingPayment', CURRENT_TIMESTAMP + INTERVAL '4 days 1 hour', NULL, 1520.00, 750.00, 7, 13),
((CURRENT_DATE + INTERVAL '14 days')::DATE, (CURRENT_DATE + INTERVAL '16 days')::DATE, 4, 'Confirmed', CURRENT_TIMESTAMP + INTERVAL '7 days 2 hours', NULL, 190.00, 150.00, 8, 14),
((CURRENT_DATE + INTERVAL '8 days')::DATE, (CURRENT_DATE + INTERVAL '10 days')::DATE, 2, 'Confirmed', CURRENT_TIMESTAMP + INTERVAL '2 days 3 hours', NULL, 330.00, 200.00, 9, 15),
((CURRENT_DATE + INTERVAL '12 days')::DATE, (CURRENT_DATE + INTERVAL '15 days')::DATE, 4, 'Rejected', CURRENT_TIMESTAMP + INTERVAL '5 days 2 hours', NULL, 420.00, 300.00, 10, 1),
((CURRENT_DATE + INTERVAL '18 days')::DATE, (CURRENT_DATE + INTERVAL '21 days')::DATE, 5, 'Confirmed', CURRENT_TIMESTAMP + INTERVAL '3 days 5 hours', NULL, 630.00, 350.00, 11, 3),
((CURRENT_DATE + INTERVAL '25 days')::DATE, (CURRENT_DATE + INTERVAL '27 days')::DATE, 3, 'Confirmed', CURRENT_TIMESTAMP + INTERVAL '8 days 1 hour', NULL, 220.00, 0.00, 12, 4),
((CURRENT_DATE + INTERVAL '16 days')::DATE, (CURRENT_DATE + INTERVAL '19 days')::DATE, 6, 'Confirmed', CURRENT_TIMESTAMP + INTERVAL '4 days 6 hours', NULL, 1650.00, 1200.00, 13, 6),
((CURRENT_DATE + INTERVAL '18 days')::DATE, (CURRENT_DATE + INTERVAL '21 days')::DATE, 5, 'Confirmed', CURRENT_TIMESTAMP + INTERVAL '6 days 3 hours', NULL, 375.00, 200.00, 14, 8),
((CURRENT_DATE + INTERVAL '30 days')::DATE, (CURRENT_DATE + INTERVAL '32 days')::DATE, 2, 'PendingPayment', CURRENT_TIMESTAMP + INTERVAL '2 days 1 hour', NULL, 170.00, 0.00, 15, 2);

INSERT INTO transactions (amount, transaction_method, type, status, paid_at, refund_due_by, refund_id, booking_id)
VALUES (450.00, 'Card', 'FullPayment', 'Completed', CURRENT_TIMESTAMP + INTERVAL '3 days 3 hours', CURRENT_TIMESTAMP + INTERVAL '10 days', NULL, 1);
INSERT INTO transactions (amount, transaction_method, type, status, paid_at, refund_due_by, refund_id, booking_id)
VALUES (200.00, 'Card', 'SecurityDeposit', 'Completed', CURRENT_TIMESTAMP + INTERVAL '3 days 3 hours', CURRENT_TIMESTAMP + INTERVAL '10 days', NULL, 1);
INSERT INTO transactions (amount, transaction_method, type, status, paid_at, refund_due_by, refund_id, booking_id)
VALUES (960.00, 'Paypal', 'FullPayment', 'Completed', CURRENT_TIMESTAMP + INTERVAL '4 days 4 hours', CURRENT_TIMESTAMP + INTERVAL '15 days', NULL, 2);
INSERT INTO transactions (amount, transaction_method, type, status, paid_at, refund_due_by, refund_id, booking_id)
VALUES (500.00, 'Paypal', 'SecurityDeposit', 'Completed', CURRENT_TIMESTAMP + INTERVAL '4 days 4 hours', CURRENT_TIMESTAMP + INTERVAL '15 days', NULL, 2);
INSERT INTO transactions (amount, transaction_method, type, status, paid_at, refund_due_by, refund_id, booking_id)
VALUES (225.00, 'Bank Transfer', 'FullPayment', 'Completed', CURRENT_TIMESTAMP + INTERVAL '2 days 2 hours', CURRENT_TIMESTAMP + INTERVAL '10 days', NULL, 3);
INSERT INTO transactions (amount, transaction_method, type, status, paid_at, refund_due_by, refund_id, booking_id)
VALUES (100.00, 'Bank Transfer', 'SecurityDeposit', 'Completed', CURRENT_TIMESTAMP + INTERVAL '2 days 2 hours', CURRENT_TIMESTAMP + INTERVAL '10 days', NULL, 3);
INSERT INTO transactions (amount, transaction_method, type, status, paid_at, refund_due_by, refund_id, booking_id)
VALUES (2250.00, 'Card', 'FullPayment', 'Completed', CURRENT_TIMESTAMP + INTERVAL '5 days 3 hours', CURRENT_TIMESTAMP + INTERVAL '19 days', NULL, 4);
INSERT INTO transactions (amount, transaction_method, type, status, paid_at, refund_due_by, refund_id, booking_id)
VALUES (1000.00, 'Card', 'SecurityDeposit', 'Completed', CURRENT_TIMESTAMP + INTERVAL '5 days 3 hours', CURRENT_TIMESTAMP + INTERVAL '19 days', NULL, 4);
INSERT INTO transactions (amount, transaction_method, type, status, paid_at, refund_due_by, refund_id, booking_id)
VALUES (360.00, 'Paypal', 'FullPayment', 'Completed', CURRENT_TIMESTAMP + INTERVAL '6 days 2 hours', CURRENT_TIMESTAMP + INTERVAL '12 days', NULL, 5);
INSERT INTO transactions (amount, transaction_method, type, status, paid_at, refund_due_by, refund_id, booking_id)
VALUES (180.00, 'Paypal', 'Refund', 'Completed', CURRENT_TIMESTAMP + INTERVAL '12 days 1 hour', CURRENT_TIMESTAMP + INTERVAL '14 days', 9, 5);
INSERT INTO transactions (amount, transaction_method, type, status, paid_at, refund_due_by, refund_id, booking_id)
VALUES (390.00, 'Card', 'FullPayment', 'Completed', CURRENT_TIMESTAMP + INTERVAL '3 days 5 hours', CURRENT_TIMESTAMP + INTERVAL '20 days', NULL, 6);
INSERT INTO transactions (amount, transaction_method, type, status, paid_at, refund_due_by, refund_id, booking_id)
VALUES (200.00, 'Card', 'SecurityDeposit', 'Completed', CURRENT_TIMESTAMP + INTERVAL '3 days 5 hours', CURRENT_TIMESTAMP + INTERVAL '20 days', NULL, 6);
INSERT INTO transactions (amount, transaction_method, type, status, paid_at, refund_due_by, refund_id, booking_id)
VALUES (1520.00, 'Bank Transfer', 'FullPayment', 'Pending', NULL, NULL, NULL, 7);
INSERT INTO transactions (amount, transaction_method, type, status, paid_at, refund_due_by, refund_id, booking_id)
VALUES (190.00, 'Card', 'FullPayment', 'Completed', CURRENT_TIMESTAMP + INTERVAL '7 days 3 hours', CURRENT_TIMESTAMP + INTERVAL '18 days', NULL, 8);
INSERT INTO transactions (amount, transaction_method, type, status, paid_at, refund_due_by, refund_id, booking_id)
VALUES (150.00, 'Card', 'SecurityDeposit', 'Completed', CURRENT_TIMESTAMP + INTERVAL '7 days 3 hours', CURRENT_TIMESTAMP + INTERVAL '18 days', NULL, 8);

INSERT INTO conversations (created_at, concluded_at, guest_id, host_id, listing_id, booking_id)
VALUES 
(CURRENT_TIMESTAMP + INTERVAL '3 days 1 hour', NULL, 5, 1, 1, 1),
(CURRENT_TIMESTAMP + INTERVAL '4 days 2 hours', CURRENT_TIMESTAMP + INTERVAL '11 days', 7, 2, 2, 2),
(CURRENT_TIMESTAMP + INTERVAL '2 days 30 minutes', CURRENT_TIMESTAMP + INTERVAL '9 days', 9, 3, 3, 3),
(CURRENT_TIMESTAMP + INTERVAL '5 days 1 hour', NULL, 10, 4, 4, 4),
(CURRENT_TIMESTAMP + INTERVAL '6 days 30 minutes', CURRENT_TIMESTAMP + INTERVAL '7 days', 11, 5, 5, 5),
(CURRENT_TIMESTAMP + INTERVAL '3 days 3 hours', NULL, 12, 6, 6, 6),
(CURRENT_TIMESTAMP + INTERVAL '4 days 30 minutes', NULL, 13, 7, 7, 7),
(CURRENT_TIMESTAMP + INTERVAL '7 days 1 hour', NULL, 14, 8, 8, 8),
(CURRENT_TIMESTAMP + INTERVAL '2 days 2 hours', NULL, 15, 9, 9, 9),
(CURRENT_TIMESTAMP + INTERVAL '5 days 1 hour', CURRENT_TIMESTAMP + INTERVAL '5 days 6 hours', 1, 10, 10, 10),
(CURRENT_TIMESTAMP + INTERVAL '3 days 4 hours', NULL, 3, 11, 11, 11),
(CURRENT_TIMESTAMP + INTERVAL '8 days 30 minutes', NULL, 4, 12, 12, 12),
(CURRENT_TIMESTAMP + INTERVAL '4 days 5 hours', NULL, 6, 13, 13, 13),
(CURRENT_TIMESTAMP + INTERVAL '6 days 2 hours', NULL, 8, 14, 14, 14),
(CURRENT_TIMESTAMP + INTERVAL '2 days 30 minutes', NULL, 2, 15, 15, 15);

INSERT INTO messages (author, content, send_at, attachments, is_read, conversation_id)
VALUES 
('Guest', 'Hi! I am interested in booking your apartment for 3 nights. Is it available?', CURRENT_TIMESTAMP + INTERVAL '3 days 2 hours', NULL, true, 1),
('Host', 'Hello! Yes, the apartment is available for those dates. Feel free to book!', CURRENT_TIMESTAMP + INTERVAL '3 days 3 hours', NULL, true, 1),
('Guest', 'Great! Just booked it. Looking forward to the stay.', CURRENT_TIMESTAMP + INTERVAL '3 days 4 hours', NULL, false, 1),

('Guest', 'Hello, I would like to know more about the beach access.', CURRENT_TIMESTAMP + INTERVAL '4 days 3 hours', NULL, true, 2),
('Host', 'The beach is directly accessible from our private gate. You will love it!', CURRENT_TIMESTAMP + INTERVAL '4 days 5 hours', NULL, true, 2),
('Guest', 'Perfect! Booking now.', CURRENT_TIMESTAMP + INTERVAL '4 days 6 hours', NULL, true, 2),
('Guest', 'Thank you so much for the wonderful stay!', CURRENT_TIMESTAMP + INTERVAL '10 days 12 hours', NULL, true, 2),
('Host', 'It was our pleasure hosting you. Come back anytime!', CURRENT_TIMESTAMP + INTERVAL '10 days 18 hours', NULL, true, 2),

('Guest', 'Is the studio close to public transportation?', CURRENT_TIMESTAMP + INTERVAL '2 days 1 hour', NULL, true, 3),
('Host', 'Yes, the subway station is just 2 blocks away.', CURRENT_TIMESTAMP + INTERVAL '2 days 2 hours', NULL, true, 3),
('Guest', 'Excellent! Confirmed my booking.', CURRENT_TIMESTAMP + INTERVAL '2 days 3 hours', NULL, true, 3),
('Guest', 'Had a great time, thanks!', CURRENT_TIMESTAMP + INTERVAL '8 days 14 hours', NULL, true, 3),

('Guest', 'Can you tell me about the hot tub and wine cellar access?', CURRENT_TIMESTAMP + INTERVAL '5 days 2 hours', NULL, true, 4),
('Host', 'Both are available for guest use. The wine cellar has a curated selection you can purchase.', CURRENT_TIMESTAMP + INTERVAL '5 days 4 hours', NULL, true, 4),
('Guest', 'Sounds amazing! Booking for our anniversary.', CURRENT_TIMESTAMP + INTERVAL '5 days 5 hours', NULL, false, 4),

('Guest', 'Hi, I need to cancel my booking due to a family emergency.', CURRENT_TIMESTAMP + INTERVAL '6 days 2 hours', NULL, true, 5),
('Host', 'I understand. I have processed the cancellation. Hope everything is okay.', CURRENT_TIMESTAMP + INTERVAL '6 days 4 hours', NULL, true, 5),

('Guest', 'Is your home pet-friendly? We have a small dog.', CURRENT_TIMESTAMP + INTERVAL '3 days 4 hours', NULL, true, 6),
('Host', 'Yes! We welcome pets. There is a $50 pet fee.', CURRENT_TIMESTAMP + INTERVAL '3 days 6 hours', NULL, true, 6),
('Guest', 'Perfect! We will bring our pup along.', CURRENT_TIMESTAMP + INTERVAL '3 days 7 hours', NULL, false, 6),

('Guest', 'I am having trouble with the payment. Can you help?', CURRENT_TIMESTAMP + INTERVAL '4 days 2 hours', NULL, true, 7),
('Host', 'Let me check with support. I will get back to you shortly.', CURRENT_TIMESTAMP + INTERVAL '4 days 4 hours', NULL, false, 7),

('Guest', 'Does the cottage have heating? Visiting in winter.', CURRENT_TIMESTAMP + INTERVAL '7 days 2 hours', NULL, true, 8),
('Host', 'Yes, there is a wood-burning fireplace and central heating.', CURRENT_TIMESTAMP + INTERVAL '7 days 4 hours', NULL, true, 8),
('Guest', 'Great, just what we need!', CURRENT_TIMESTAMP + INTERVAL '7 days 5 hours', NULL, false, 8),

('Guest', 'I am an artist. Is there good natural light in the loft?', CURRENT_TIMESTAMP + INTERVAL '2 days 3 hours', NULL, true, 9),
('Host', 'Absolutely! Large windows with northern light exposure. Perfect for artists.', CURRENT_TIMESTAMP + INTERVAL '2 days 5 hours', NULL, true, 9),
('Guest', 'That is exactly what I need. Booking now!', CURRENT_TIMESTAMP + INTERVAL '2 days 6 hours', NULL, false, 9),

('Guest', 'Hello, I would like to book your heritage house.', CURRENT_TIMESTAMP + INTERVAL '5 days 2 hours', NULL, true, 10),
('Host', 'Unfortunately those dates are not available anymore. My apologies.', CURRENT_TIMESTAMP + INTERVAL '5 days 4 hours', NULL, true, 10),
('Guest', 'No problem, thank you for letting me know.', CURRENT_TIMESTAMP + INTERVAL '5 days 5 hours', NULL, true, 10),

('Guest', 'What water sports equipment is included?', CURRENT_TIMESTAMP + INTERVAL '3 days 5 hours', NULL, true, 11),
('Host', 'We provide kayaks, paddleboards, and snorkeling gear.', CURRENT_TIMESTAMP + INTERVAL '3 days 7 hours', NULL, true, 11),
('Guest', 'Awesome! Cannot wait to try them out.', CURRENT_TIMESTAMP + INTERVAL '3 days 8 hours', NULL, false, 11),

('Guest', 'Is the cabin truly secluded? Looking for peace and quiet.', CURRENT_TIMESTAMP + INTERVAL '8 days 2 hours', NULL, true, 12),
('Host', 'Yes, the nearest neighbor is over a mile away. Very peaceful.', CURRENT_TIMESTAMP + INTERVAL '8 days 4 hours', NULL, true, 12),
('Guest', 'Perfect for our digital detox!', CURRENT_TIMESTAMP + INTERVAL '8 days 5 hours', NULL, false, 12),

('Guest', 'Does the penthouse have valet parking included?', CURRENT_TIMESTAMP + INTERVAL '4 days 6 hours', NULL, true, 13),
('Host', 'Yes, complimentary valet parking for all guests.', CURRENT_TIMESTAMP + INTERVAL '4 days 8 hours', NULL, true, 13),
('Guest', 'Excellent! That seals the deal.', CURRENT_TIMESTAMP + INTERVAL '4 days 9 hours', NULL, false, 13),

('Guest', 'Are there board games for kids? Ages 6 and 8.', CURRENT_TIMESTAMP + INTERVAL '6 days 3 hours', NULL, true, 14),
('Host', 'Yes! We have games suitable for all ages. The kids will love it.', CURRENT_TIMESTAMP + INTERVAL '6 days 5 hours', NULL, true, 14),
('Guest', 'Great, they will be so excited!', CURRENT_TIMESTAMP + INTERVAL '6 days 6 hours', NULL, false, 14),

('Guest', 'What does off-grid mean exactly? Is there electricity?', CURRENT_TIMESTAMP + INTERVAL '2 days 2 hours', NULL, true, 15),
('Host', 'Solar power provides electricity. You will have lights, outlets, etc. Just eco-friendly!', CURRENT_TIMESTAMP + INTERVAL '2 days 4 hours', NULL, true, 15),
('Guest', 'Sounds like an adventure! Trying to complete payment now.', CURRENT_TIMESTAMP + INTERVAL '2 days 5 hours', NULL, false, 15);

INSERT INTO notifications (type, content, created_at, is_read, user_id, sender_id)
VALUES 
-- Booking confirmations for guests
('BookingConfirmed', 'Your booking at Modern Downtown Apartment has been confirmed for November 9-12.', CURRENT_TIMESTAMP + INTERVAL '3 days 2 hours 5 minutes', true, 5, NULL),
('BookingConfirmed', 'Your booking at Beachfront Paradise Villa has been confirmed for November 14-17.', CURRENT_TIMESTAMP + INTERVAL '4 days 3 hours 5 minutes', true, 7, NULL),
('BookingConfirmed', 'Your booking at Cozy City Studio has been confirmed for November 9-12.', CURRENT_TIMESTAMP + INTERVAL '2 days 1 hour 5 minutes', true, 9, NULL),
('BookingConfirmed', 'Your booking at Luxury Mountain Retreat has been confirmed for November 16-21.', CURRENT_TIMESTAMP + INTERVAL '5 days 2 hours 5 minutes', true, 10, NULL),

-- Booking cancellation
('BookingCanceled', 'Your booking at Historic Downtown Loft has been cancelled as requested.', CURRENT_TIMESTAMP + INTERVAL '6 days 2 hours 5 minutes', true, 11, NULL),

-- Payment received notifications for hosts
('PaymentReceived', 'Payment of $450.00 received for booking at Modern Downtown Apartment.', CURRENT_TIMESTAMP + INTERVAL '3 days 2 hours 10 minutes', true, 1, NULL),
('PaymentReceived', 'Payment of $960.00 received for booking at Beachfront Paradise Villa.', CURRENT_TIMESTAMP + INTERVAL '4 days 3 hours 10 minutes', true, 2, NULL),
('PaymentReceived', 'Payment of $225.00 received for booking at Cozy City Studio.', CURRENT_TIMESTAMP + INTERVAL '2 days 1 hour 10 minutes', true, 3, NULL),
('PaymentReceived', 'Payment of $2250.00 received for booking at Luxury Mountain Retreat.', CURRENT_TIMESTAMP + INTERVAL '5 days 2 hours 10 minutes', true, 4, NULL),

-- Message received notifications
('MessageReceived', 'You have a new message from your guest about Modern Downtown Apartment.', CURRENT_TIMESTAMP + INTERVAL '3 days 2 hours 1 minute', true, 1, NULL),
('MessageReceived', 'You have a new message from your host about Beachfront Paradise Villa.', CURRENT_TIMESTAMP + INTERVAL '4 days 5 hours 1 minute', true, 7, NULL),
('MessageReceived', 'You have a new message from your guest about Cozy City Studio.', CURRENT_TIMESTAMP + INTERVAL '2 days 1 hour 1 minute', true, 3, NULL),
('MessageReceived', 'You have a new message from your host about Luxury Mountain Retreat.', CURRENT_TIMESTAMP + INTERVAL '5 days 4 hours 1 minute', true, 10, NULL),

-- Remainder notifications for upcoming check-ins
('Remainder', 'Reminder: Your check-in at Modern Downtown Apartment is tomorrow at 3:00 PM.', CURRENT_TIMESTAMP + INTERVAL '4 days', false, 5, NULL),
('Remainder', 'Reminder: Your check-in at Beachfront Paradise Villa is in 2 days at 4:00 PM.', CURRENT_TIMESTAMP + INTERVAL '8 days', false, 7, NULL),
('Remainder', 'Reminder: Your guest checks in tomorrow at Cozy City Studio.', CURRENT_TIMESTAMP + INTERVAL '4 days', false, 3, NULL),

-- Review left notifications for hosts
('ReviewLeft', 'You received a new 5-star review from your guest for Modern Downtown Apartment.', CURRENT_TIMESTAMP + INTERVAL '8 days 2 hours 10 minutes', false, 1, NULL),
('ReviewLeft', 'You received a new 5-star review from your guest for Beachfront Paradise Villa.', CURRENT_TIMESTAMP + INTERVAL '14 days 3 hours 10 minutes', false, 2, NULL),
('ReviewLeft', 'You received a new 4-star review from your guest for Cozy City Studio.', CURRENT_TIMESTAMP + INTERVAL '8 days 4 hours 10 minutes', false, 3, NULL),
('ReviewLeft', 'You received a new 5-star review from your guest for Luxury Mountain Retreat.', CURRENT_TIMESTAMP + INTERVAL '17 days 2 hours 10 minutes', false, 4, NULL),

-- Admin notices (assuming admin_id 1 exists)
('AdminNotice', 'Welcome to our platform! Please complete your profile to start hosting or booking.', CURRENT_TIMESTAMP + INTERVAL '1 hour', true, 1, 1),
('AdminNotice', 'New feature: You can now add up to 20 photos to your listings!', CURRENT_TIMESTAMP + INTERVAL '1 day', false, 2, 1),
('AdminNotice', 'Reminder: Please verify your email address to unlock all platform features.', CURRENT_TIMESTAMP + INTERVAL '2 days', false, 5, 1),
('AdminNotice', 'Your listing has been approved and is now live on the platform.', CURRENT_TIMESTAMP + INTERVAL '3 days', true, 6, 1),
('AdminNotice', 'Platform maintenance scheduled for this weekend. Expect brief downtime.', CURRENT_TIMESTAMP + INTERVAL '5 days', false, 10, 1),

-- Additional notifications for various users
('MessageReceived', 'You have a new message from your guest about Family-Friendly Suburban Home.', CURRENT_TIMESTAMP + INTERVAL '3 days 4 hours 1 minute', true, 6, NULL),
('BookingConfirmed', 'Your booking at Elegant Countryside Villa has been confirmed for November 24-28.', CURRENT_TIMESTAMP + INTERVAL '4 days 1 hour 5 minutes', false, 13, NULL),
('MessageReceived', 'You have a new message from your host about Rustic Country Cottage.', CURRENT_TIMESTAMP + INTERVAL '7 days 4 hours 1 minute', false, 14, NULL),
('BookingConfirmed', 'Your booking at Urban Artist Loft has been confirmed for November 12-14.', CURRENT_TIMESTAMP + INTERVAL '2 days 3 hours 5 minutes', true, 15, NULL),
('PaymentReceived', 'Payment of $630.00 received for booking at Tropical Beach Bungalow.', CURRENT_TIMESTAMP + INTERVAL '3 days 5 hours 10 minutes', true, 11, NULL);

INSERT INTO reviews (rating, comment, created_at, answer_content, answered_at, guest_id, host_id, booking_id)
VALUES 
(5, 'Amazing apartment. Very clean and exactly as described. The location was perfect for exploring downtown. Would definitely stay again.', 
 (CURRENT_DATE + INTERVAL '9 days')::TIMESTAMP + INTERVAL '5 hours', 
 'Thank you so much for your kind words. We are delighted you enjoyed your stay. You were wonderful guests and you are welcome back anytime.', 
 (CURRENT_DATE + INTERVAL '8 days')::TIMESTAMP + INTERVAL '12 hours', 
 9, 3, 3),

(5, 'Stunning beachfront property. Waking up to ocean views was incredible. The villa had everything we needed and more. Highly recommended.', 
 (CURRENT_DATE + INTERVAL '13 days')::TIMESTAMP + INTERVAL '10 hours', 
 'We appreciate your wonderful review. So happy you enjoyed the ocean views and the amenities. Hope to host you again soon.', 
 (CURRENT_DATE + INTERVAL '14 days')::TIMESTAMP + INTERVAL '16 hours', 
 7, 2, 2),

(4, 'Nice little studio in a great location. Very cozy and comfortable for two people. Only minor issue was the wifi was a bit slow.', 
 (CURRENT_DATE + INTERVAL '9 days')::TIMESTAMP + INTERVAL '14 hours', 
 NULL, 
 NULL, 
 9, 3, 3),

(5, 'Absolutely spectacular mountain retreat. Every detail was perfect. The views, amenities, and service exceeded our expectations. Worth every penny.', 
 (CURRENT_DATE + INTERVAL '13 days')::TIMESTAMP + INTERVAL '8 hours', 
 'Thank you for this amazing review. We strive for perfection and are thrilled we met your expectations. Come back soon.', 
 (CURRENT_DATE + INTERVAL '17 days')::TIMESTAMP + INTERVAL '18 hours', 
 7, 2, 2),

(5, 'Perfect home for our family vacation. The kids loved the backyard and the neighborhood was safe and quiet. Great communication with the host.', 
 (CURRENT_DATE + INTERVAL '9 days')::TIMESTAMP + INTERVAL '6 hours', 
 'So glad your family had a wonderful time. The kids are always welcome. Thank you for being such considerate guests.', 
 (CURRENT_DATE + INTERVAL '19 days')::TIMESTAMP + INTERVAL '15 hours', 
 9, 3, 3),

(3, 'The cottage was nice overall but had some maintenance issues. The heating was not working properly and it got quite cold at night.', 
 (CURRENT_DATE + INTERVAL '13 days')::TIMESTAMP + INTERVAL '11 hours', 
 'We apologize for the heating issue. A technician has been called to fix it immediately. We hope you can give us another chance in the future.', 
 (CURRENT_DATE + INTERVAL '16 days')::TIMESTAMP + INTERVAL '20 hours', 
 7, 2, 2),

(5, 'This loft is a dream for any creative. The natural light and open space were incredibly inspiring. Loved the arts district location too.', 
 (CURRENT_DATE + INTERVAL '13 days')::TIMESTAMP + INTERVAL '9 hours', 
 NULL, 
 NULL, 
 7, 2, 2),

(5, 'Paradise found. Direct beach access, beautiful sunsets, and all the water sports equipment we needed. Could not ask for more.', 
 (CURRENT_DATE + INTERVAL '9 days')::TIMESTAMP + INTERVAL '7 hours', 
 'Thrilled you experienced paradise at our bungalow. Your enjoyment makes all our efforts worthwhile. See you next summer.', 
 (CURRENT_DATE + INTERVAL '22 days')::TIMESTAMP + INTERVAL '14 hours', 
 9, 3, 3),

(5, 'The most luxurious stay we have ever experienced. The rooftop terrace views were breathtaking. Concierge service was outstanding. Five stars.', 
 (CURRENT_DATE + INTERVAL '13 days')::TIMESTAMP + INTERVAL '10 hours', 
 'We are honored by your review. Providing exceptional luxury experiences is our passion. Thank you for choosing our penthouse.', 
 (CURRENT_DATE + INTERVAL '20 days')::TIMESTAMP + INTERVAL '19 hours', 
 7, 2, 2),

(4, 'Beautiful garden and very comfortable home. Great for families. Only wish we could have stayed longer. The outdoor area was fantastic.', 
 (CURRENT_DATE + INTERVAL '9 days')::TIMESTAMP + INTERVAL '13 hours', 
 'Thank you for your lovely review. We are so happy you enjoyed the garden. Extended stays are always welcome.', 
 (CURRENT_DATE + INTERVAL '21 days')::TIMESTAMP + INTERVAL '22 hours', 
 9, 3, 3),

(4, 'Solid apartment in an excellent location. Walking distance to everything. Clean and well-maintained. Would recommend.', 
 (CURRENT_DATE + INTERVAL '13 days')::TIMESTAMP + INTERVAL '8 hours', 
 NULL, 
 NULL, 
 7, 2, 2),

(5, 'Our best vacation ever. The villa was immaculate and the beach access made it perfect for the kids. Already planning our next visit.', 
 (CURRENT_DATE + INTERVAL '13 days')::TIMESTAMP + INTERVAL '11 hours', 
 NULL, 
 NULL, 
 7, 2, 2),

(4, 'Great value for money. The studio had everything we needed for our city break. Check-in was smooth and host was very responsive.', 
 (CURRENT_DATE + INTERVAL '9 days')::TIMESTAMP + INTERVAL '15 hours', 
 'Thank you. We aim to provide great value and smooth experiences. You are welcome back anytime.', 
 (CURRENT_DATE + INTERVAL '10 days')::TIMESTAMP + INTERVAL '21 hours', 
 9, 3, 3),

(5, 'The mountain views from this property are simply unbeatable. Everything was top-notch from start to finish. Highly recommend.', 
 (CURRENT_DATE + INTERVAL '9 days')::TIMESTAMP + INTERVAL '12 hours', 
 NULL, 
 NULL, 
 9, 3, 3),

(5, 'Wonderful experience for our family. The house was spotless, well-equipped, and the kids had so much fun in the backyard. Thank you.', 
 (CURRENT_DATE + INTERVAL '13 days')::TIMESTAMP + INTERVAL '9 hours', 
 'Your family was a pleasure to host. We love seeing families enjoy our space. Hope to welcome you back soon.', 
 (CURRENT_DATE + INTERVAL '20 days')::TIMESTAMP + INTERVAL '17 hours', 
 7, 2, 2);
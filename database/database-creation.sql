DROP Schema IF EXISTS lbaw2581 CASCADE;

CREATE SCHEMA IF NOT EXISTS lbaw2581;
SET search_path TO lbaw2581;
show search_path;

-- PostgreSQL Database Schema for HotSpotz

-- R01: User table
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL CHECK (full_name <> ''),
    email VARCHAR(255) NOT NULL UNIQUE CHECK (email LIKE '%@%._%'),
    password_hash VARCHAR(255) NOT NULL CHECK (password_hash <> ''),
    date_of_birth DATE NOT NULL CHECK (date_of_birth <= CURRENT_DATE - INTERVAL '18 years'),
    phone VARCHAR(20) CHECK (phone ~ '^\+[0-9]{1,3}[0-9]{4,14}$'),
    profile_picture_url TEXT CHECK (profile_picture_url IS NULL OR profile_picture_url <> ''),
    registration_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    is_blocked BOOLEAN NOT NULL DEFAULT false,
    is_active BOOLEAN NOT NULL DEFAULT false,
    CHECK (registration_date::DATE >= date_of_birth)
);

-- R02: Admin table
CREATE TABLE admins (
    id SERIAL PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL CHECK (full_name <> ''),
    email VARCHAR(255) NOT NULL UNIQUE CHECK (email LIKE '%@%._%'),
    password_hash VARCHAR(255) NOT NULL CHECK (password_hash <> ''),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- R03: Host profile table
CREATE TABLE host_profiles (
    id SERIAL PRIMARY KEY,
    answers TEXT NOT NULL CHECK (answers <> ''),
    verification_docs TEXT UNIQUE,
    application_status VARCHAR(20) NOT NULL CHECK (application_status IN ('Pending', 'Approved', 'Rejected')) DEFAULT 'Pending',
    is_verified BOOLEAN NOT NULL,
    payout_details TEXT,
    submited_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    verified_at TIMESTAMP,
    user_id INTEGER NOT NULL UNIQUE REFERENCES users(id) ON DELETE CASCADE,
    verifier_id INTEGER REFERENCES admins(id) ON DELETE SET NULL,
    
    CHECK (application_status <> 'Approved' OR payout_details IS NOT NULL),
    CHECK (application_status <> 'Approved' OR (verified_at IS NOT NULL AND verified_at >= submited_at))
);

-- Trigger to validate host_profiles.submited_at >= user.registration_date
CREATE OR REPLACE FUNCTION check_host_profile_submited_at()
RETURNS TRIGGER AS $$
BEGIN
    IF NEW.submited_at < (SELECT registration_date FROM users WHERE id = NEW.user_id) THEN
        RAISE EXCEPTION 'submited_at must be >= user registration_date';
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_check_host_profile_submited_at
    BEFORE INSERT OR UPDATE ON host_profiles
    FOR EACH ROW
    EXECUTE FUNCTION check_host_profile_submited_at();

-- R04: Listing table
CREATE TABLE listings (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL CHECK (title <> ''),
    description TEXT NOT NULL CHECK (description <> ''),
    price_per_night NUMERIC(10,2) NOT NULL CHECK (price_per_night >= 0),
    max_occupancy INTEGER NOT NULL CHECK (max_occupancy >= 1) DEFAULT 1,
    location_address TEXT NOT NULL CHECK (location_address <> ''),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN NOT NULL DEFAULT true,
    minimum_stay INTEGER NOT NULL CHECK (minimum_stay >= 1) DEFAULT 1,
    cancellation_policy TEXT NOT NULL CHECK (cancellation_policy <> ''),
    security_deposit_amount NUMERIC(10,2) CHECK (security_deposit_amount IS NULL OR security_deposit_amount >= 0),
    check_in_time TIME NOT NULL,
    check_out_time TIME NOT NULL,
    average_rating NUMERIC(3,2) NOT NULL DEFAULT 0,
    host_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE
);

-- R05: Listing photo table
CREATE TABLE listing_photos (
    id SERIAL PRIMARY KEY,
    url TEXT NOT NULL UNIQUE CHECK (url <> ''),
    caption TEXT,
    uploaded_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    listing_id INTEGER NOT NULL REFERENCES listings(id) ON DELETE CASCADE
);

-- Trigger to validate listing_photos.uploaded_at >= listing.created_at
CREATE OR REPLACE FUNCTION check_listing_photo_uploaded_at()
RETURNS TRIGGER AS $$
BEGIN
    IF NEW.uploaded_at < (SELECT created_at FROM listings WHERE id = NEW.listing_id) THEN
        RAISE EXCEPTION 'uploaded_at must be >= listing created_at';
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_check_listing_photo_uploaded_at
    BEFORE INSERT OR UPDATE ON listing_photos
    FOR EACH ROW
    EXECUTE FUNCTION check_listing_photo_uploaded_at();

-- R06: Blocked date table
CREATE TABLE blocked_dates (
    id SERIAL PRIMARY KEY,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL CHECK (end_date >= start_date),
    notes TEXT,
    listing_id INTEGER NOT NULL REFERENCES listings(id) ON DELETE CASCADE
);

-- Trigger to validate blocked_dates.start_date >= listing.created_at
CREATE OR REPLACE FUNCTION check_blocked_date_start_date()
RETURNS TRIGGER AS $$
BEGIN
    IF NEW.start_date < (SELECT created_at::DATE FROM listings WHERE id = NEW.listing_id) THEN
        RAISE EXCEPTION 'start_date must be >= listing created_at';
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_check_blocked_date_start_date
    BEFORE INSERT OR UPDATE ON blocked_dates
    FOR EACH ROW
    EXECUTE FUNCTION check_blocked_date_start_date();

-- R07: Booking table
CREATE TABLE bookings (
    id SERIAL PRIMARY KEY,
    check_in_date DATE NOT NULL,
    check_out_date DATE NOT NULL CHECK (check_out_date > check_in_date),
    num_guests INTEGER NOT NULL CHECK (num_guests >= 1) DEFAULT 1,
    status VARCHAR(20) NOT NULL CHECK (status IN ('PendingPayment', 'Confirmed', 'Cancelled', 'Completed', 'Rejected')) DEFAULT 'PendingPayment',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    security_deposit_held NUMERIC(10,2),
    total_price NUMERIC(10,2) NOT NULL CHECK (total_price >= 0),
    security_deposit NUMERIC(10,2) NOT NULL CHECK (security_deposit >= 0),
    listing_id INTEGER NOT NULL REFERENCES listings(id) ON DELETE CASCADE,
    guest_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    
    UNIQUE (check_in_date, check_out_date, listing_id)
);

-- Trigger to validate booking constraints
CREATE OR REPLACE FUNCTION check_booking_constraints()
RETURNS TRIGGER AS $$
DECLARE
    listing_created_at DATE;
    listing_host_id INTEGER;
BEGIN
    -- Get listing details
    SELECT created_at::DATE, host_id INTO listing_created_at, listing_host_id
    FROM listings WHERE id = NEW.listing_id;
    
    -- Check check_in_date >= listing.created_at
    IF NEW.check_in_date < listing_created_at THEN
        RAISE EXCEPTION 'check_in_date must be >= listing created_at';
    END IF;
    
    -- Check guest_id <> host_id
    IF NEW.guest_id = listing_host_id THEN
        RAISE EXCEPTION 'guest cannot book their own listing';
    END IF;
    
    -- Check security_deposit_held if check_out_date + 2 days has passed
    IF CURRENT_DATE >= NEW.check_out_date + INTERVAL '2 days' AND NEW.security_deposit_held IS NULL THEN
        RAISE EXCEPTION 'security_deposit_held must be set after check_out_date + 2 days';
    END IF;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_check_booking_constraints
    BEFORE INSERT OR UPDATE ON bookings
    FOR EACH ROW
    EXECUTE FUNCTION check_booking_constraints();

-- R08: Transaction table
CREATE TABLE transactions (
    id SERIAL PRIMARY KEY,
    amount NUMERIC(10,2) NOT NULL CHECK (amount >= 0),
    transaction_method VARCHAR(20) NOT NULL CHECK (transaction_method IN ('Card', 'Bank Transfer', 'Paypal', 'Cash')),
    type VARCHAR(20) NOT NULL CHECK (type IN ('FullPayment', 'SecurityDeposit', 'Refund')),
    status VARCHAR(20) NOT NULL CHECK (status IN ('Pending', 'Completed', 'Failed', 'Refunded')) DEFAULT 'Pending',
    paid_at TIMESTAMP,
    refund_due_by TIMESTAMP,
    refund_id INTEGER REFERENCES transactions(id) ON DELETE SET NULL,
    booking_id INTEGER NOT NULL REFERENCES bookings(id) ON DELETE CASCADE,
    
    CHECK ((status NOT IN ('Completed', 'Refunded')) OR paid_at IS NOT NULL),
    CHECK ((status NOT IN ('Completed', 'Refunded')) OR refund_due_by IS NOT NULL)
);

-- Trigger to validate transaction constraints
CREATE OR REPLACE FUNCTION check_transaction_constraints()
RETURNS TRIGGER AS $$
DECLARE
    booking_created_at TIMESTAMP;
    refund_transaction_type VARCHAR(20);
    refund_due_by_date TIMESTAMP;
BEGIN
    -- Check paid_at >= booking.created_at
    IF NEW.paid_at IS NOT NULL THEN
        SELECT created_at INTO booking_created_at FROM bookings WHERE id = NEW.booking_id;
        IF NEW.paid_at < booking_created_at THEN
            RAISE EXCEPTION 'paid_at must be >= booking created_at';
        END IF;
    END IF;
    
    -- Check refund_id constraints
    IF NEW.refund_id IS NOT NULL THEN
        SELECT type, refund_due_by INTO refund_transaction_type, refund_due_by_date
        FROM transactions WHERE id = NEW.refund_id;
        
        IF refund_transaction_type = 'Refunded' THEN
            RAISE EXCEPTION 'refund_id cannot reference a transaction of type Refunded';
        END IF;
        
        IF NEW.paid_at < refund_due_by_date THEN
            RAISE EXCEPTION 'paid_at must be >= refund_id.refund_due_by';
        END IF;
    END IF;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_check_transaction_constraints
    BEFORE INSERT OR UPDATE ON transactions
    FOR EACH ROW
    EXECUTE FUNCTION check_transaction_constraints();

-- R09: Review table
CREATE TABLE reviews (
    id SERIAL PRIMARY KEY,
    rating INTEGER NOT NULL CHECK (rating >= 0 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    answer_content TEXT,
    answered_at TIMESTAMP,
    guest_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    host_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    booking_id INTEGER NOT NULL REFERENCES bookings(id) ON DELETE CASCADE,
    
    CHECK (answer_content IS NULL OR answer_content = '' OR answered_at IS NOT NULL)
);

-- Trigger to validate review constraints
CREATE OR REPLACE FUNCTION check_review_constraints()
RETURNS TRIGGER AS $$
DECLARE
    booking_check_out_date TIMESTAMP;
    booking_guest_id INTEGER;
    booking_status VARCHAR(20);
    booking_listing_id INTEGER;
    listing_host_id INTEGER;
BEGIN
    -- Get booking and listing details
    SELECT check_out_date, guest_id, status, listing_id 
    INTO booking_check_out_date, booking_guest_id, booking_status, booking_listing_id
    FROM bookings WHERE id = NEW.booking_id;
    
    SELECT host_id INTO listing_host_id FROM listings WHERE id = booking_listing_id;
    
    -- Check created_at >= check_out_date
    IF NEW.created_at < booking_check_out_date THEN
        RAISE EXCEPTION 'review created_at must be >= booking check_out_date';
    END IF;
    
    -- Check created_at <= check_out_date + 2 days
    IF NEW.created_at > booking_check_out_date + INTERVAL '2 days' THEN
        RAISE EXCEPTION 'review must be created within 2 days of check_out_date';
    END IF;
    
    -- Check guest_id matches booking
    IF NEW.guest_id <> booking_guest_id THEN
        RAISE EXCEPTION 'review guest_id must match booking guest_id';
    END IF;
    
    -- Check booking status is Completed
    IF booking_status <> 'Completed' THEN
        RAISE EXCEPTION 'can only review completed bookings';
    END IF;
    
    -- Check host_id matches listing host
    IF NEW.host_id <> listing_host_id THEN
        RAISE EXCEPTION 'review host_id must match listing host_id';
    END IF;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_check_review_constraints
    BEFORE INSERT OR UPDATE ON reviews
    FOR EACH ROW
    EXECUTE FUNCTION check_review_constraints();

-- R10: Conversation table
CREATE TABLE conversations (
    id SERIAL PRIMARY KEY,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    concluded_at TIMESTAMP,
    guest_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    host_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE CHECK (host_id <> guest_id),
    listing_id INTEGER REFERENCES listings(id) ON DELETE CASCADE,
    booking_id INTEGER REFERENCES bookings(id) ON DELETE CASCADE,
    
    CHECK (listing_id IS NULL OR booking_id IS NOT NULL),
    CHECK (listing_id IS NOT NULL OR booking_id IS NULL),
    
    UNIQUE (guest_id, host_id, listing_id, booking_id)
);

-- Trigger to validate conversation constraints
CREATE OR REPLACE FUNCTION check_conversation_constraints()
RETURNS TRIGGER AS $$
DECLARE
    listing_host_id INTEGER;
    booking_listing_id INTEGER;
    booking_host_id INTEGER;
    booking_guest_id INTEGER;
BEGIN
    -- Check listing_id constraints
    IF NEW.listing_id IS NOT NULL THEN
        SELECT host_id INTO listing_host_id FROM listings WHERE id = NEW.listing_id;
        IF listing_host_id <> NEW.host_id THEN
            RAISE EXCEPTION 'listing host_id must match conversation host_id';
        END IF;
    END IF;
    
    -- Check booking_id constraints
    IF NEW.booking_id IS NOT NULL THEN
        SELECT listing_id, guest_id INTO booking_listing_id, booking_guest_id
        FROM bookings WHERE id = NEW.booking_id;
        
        SELECT host_id INTO booking_host_id FROM listings WHERE id = booking_listing_id;
        
        IF booking_host_id <> NEW.host_id THEN
            RAISE EXCEPTION 'booking listing host_id must match conversation host_id';
        END IF;
        
        IF booking_guest_id <> NEW.guest_id THEN
            RAISE EXCEPTION 'booking guest_id must match conversation guest_id';
        END IF;
    END IF;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_check_conversation_constraints
    BEFORE INSERT OR UPDATE ON conversations
    FOR EACH ROW
    EXECUTE FUNCTION check_conversation_constraints();

-- R11: Message table
CREATE TABLE messages (
    id SERIAL PRIMARY KEY,
    author VARCHAR(10) NOT NULL CHECK (author IN ('Guest', 'Host')),
    content TEXT NOT NULL CHECK (content <> ''),
    send_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    attachments TEXT CHECK (attachments IS NULL OR attachments <> ''),
    is_read BOOLEAN NOT NULL DEFAULT false,
    conversation_id INTEGER NOT NULL REFERENCES conversations(id) ON DELETE CASCADE
);

-- Trigger to validate message constraints
CREATE OR REPLACE FUNCTION check_message_constraints()
RETURNS TRIGGER AS $$
DECLARE
    conversation_created_at TIMESTAMP;
    conversation_concluded_at TIMESTAMP;
BEGIN
    SELECT created_at, concluded_at INTO conversation_created_at, conversation_concluded_at
    FROM conversations WHERE id = NEW.conversation_id;
    
    -- Check send_at >= conversation.created_at
    IF NEW.send_at < conversation_created_at THEN
        RAISE EXCEPTION 'message send_at must be >= conversation created_at';
    END IF;
    
    -- Check send_at <= conversation.concluded_at (if not null)
    IF conversation_concluded_at IS NOT NULL AND NEW.send_at > conversation_concluded_at THEN
        RAISE EXCEPTION 'message send_at must be <= conversation concluded_at';
    END IF;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_check_message_constraints
    BEFORE INSERT OR UPDATE ON messages
    FOR EACH ROW
    EXECUTE FUNCTION check_message_constraints();

-- R12: Notification table
CREATE TABLE notifications (
    id SERIAL PRIMARY KEY,
    type VARCHAR(30) NOT NULL CHECK (type IN ('BookingConfirmed', 'BookingCanceled', 'PaymentReceived', 'Remainder', 'MessageReceived', 'ReviewLeft', 'AdminNotice')),
    content TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    is_read BOOLEAN NOT NULL DEFAULT false,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    sender_id INTEGER REFERENCES admins(id) ON DELETE SET NULL
);

-- Trigger to validate notification constraints
CREATE OR REPLACE FUNCTION check_notification_constraints()
RETURNS TRIGGER AS $$
DECLARE
    user_registration_date TIMESTAMP;
BEGIN
    SELECT registration_date INTO user_registration_date FROM users WHERE id = NEW.user_id;
    
    IF NEW.created_at < user_registration_date THEN
        RAISE EXCEPTION 'notification created_at must be >= user registration_date';
    END IF;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_check_notification_constraints
    BEFORE INSERT OR UPDATE ON notifications
    FOR EACH ROW
    EXECUTE FUNCTION check_notification_constraints();

-- Create indexes for better query performance
CREATE INDEX idx_user_email ON users(email);
CREATE INDEX idx_listing_host ON listings(host_id);
CREATE INDEX idx_listing_active ON listings(is_active);
CREATE INDEX idx_booking_guest ON bookings(guest_id);
CREATE INDEX idx_booking_listing ON bookings(listing_id);
CREATE INDEX idx_booking_dates ON bookings(check_in_date, check_out_date);
CREATE INDEX idx_transaction_booking ON transactions(booking_id);
CREATE INDEX idx_review_booking ON reviews(booking_id);
CREATE INDEX idx_conversation_guest ON conversations(guest_id);
CREATE INDEX idx_conversation_host ON conversations(host_id);
CREATE INDEX idx_message_conversation ON messages(conversation_id);
CREATE INDEX idx_notification_user ON notifications(user_id);
CREATE INDEX idx_notification_unread ON notifications(user_id, is_read);
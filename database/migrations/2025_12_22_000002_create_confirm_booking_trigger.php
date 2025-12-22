<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Create function
        DB::unprepared(<<<'SQL'
        CREATE OR REPLACE FUNCTION confirm_booking_when_fullpayment()
        RETURNS TRIGGER AS $$
        DECLARE
            total_paid NUMERIC;
            booking_total NUMERIC;
        BEGIN
            -- Only act when a transaction becomes Completed
            IF (TG_OP = 'INSERT' OR TG_OP = 'UPDATE') AND NEW.status = 'Completed' THEN
                SELECT COALESCE(SUM(amount),0) INTO total_paid FROM transactions
                    WHERE booking_id = NEW.booking_id AND type = 'FullPayment' AND status = 'Completed';

                SELECT total_price INTO booking_total FROM bookings WHERE id = NEW.booking_id;

                IF booking_total IS NOT NULL AND total_paid >= booking_total THEN
                    UPDATE bookings SET status = 'Confirmed' WHERE id = NEW.booking_id;
                END IF;
            END IF;
            RETURN NEW;
        END;
        $$ LANGUAGE plpgsql;
        SQL
        );

        DB::unprepared(<<<'SQL'
        DROP TRIGGER IF EXISTS trigger_confirm_booking_on_payment ON transactions;
        CREATE TRIGGER trigger_confirm_booking_on_payment
            AFTER INSERT OR UPDATE ON transactions
            FOR EACH ROW
            EXECUTE FUNCTION confirm_booking_when_fullpayment();
        SQL
        );
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trigger_confirm_booking_on_payment ON transactions');
        DB::unprepared('DROP FUNCTION IF EXISTS confirm_booking_when_fullpayment()');
    }
};

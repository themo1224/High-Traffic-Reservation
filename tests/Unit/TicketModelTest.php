<?php

namespace Tests\Unit;

use App\Models\Ticket;
use PHPUnit\Framework\TestCase;

class TicketModelTest extends TestCase
{
    public function test_default_is_available()
    {
        $ticket = new Ticket();

        $this->assertTrue($ticket->isAvailable());
        $this->assertFalse($ticket->isReserved());
        $this->assertFalse($ticket->isSold());
    }

    public function test_reserve_sets_status_and_user()
    {
        $ticket = new Ticket(['ticket_number' => 1, 'seat_row' => 1, 'seat_number' => 1]);

        $ticket->reserve(42);

        $this->assertTrue($ticket->isReserved());
        $this->assertEquals(42, $ticket->user_id);
    }

    public function test_buy_sets_status_and_user()
    {
        $ticket = new Ticket(['ticket_number' => 1]);

        $ticket->buy(7);

        $this->assertTrue($ticket->isSold());
        $this->assertEquals(7, $ticket->user_id);
    }

    public function test_release_resets_status_and_user()
    {
        $ticket = new Ticket(['ticket_number' => 1]);
        $ticket->reserve(3);

        $this->assertTrue($ticket->isReserved());

        $ticket->release();

        $this->assertTrue($ticket->isAvailable());
        $this->assertNull($ticket->user_id);
    }

    public function test_seat_label()
    {
        $ticket = new Ticket(['seat_row' => 2, 'seat_number' => 5]);

        $this->assertStringContainsString('Row 2', $ticket->seatLabel());
        $this->assertStringContainsString('Seat 5', $ticket->seatLabel());
    }
}

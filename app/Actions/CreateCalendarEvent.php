<?php

namespace App\Actions;

use Spatie\GoogleCalendar\Event;
use Google\Service\Calendar\EventReminder;
use Google\Service\Calendar\EventReminders;

class CreateCalendarEvent
{
    public function execute($name, $description, $startDateTime, $endDateTime)
    {
        if (empty($startDateTime) || empty($endDateTime)) {
            throw new \InvalidArgumentException('Start and end date/time are required to create a calendar event.');
        }

        $event = new Event;
        $event->name = $name;
        $event->description = $description;
        $event->startDateTime = $startDateTime;
        $event->endDateTime = $endDateTime;

        $reminder1 = new EventReminder();
        $reminder1->setMethod('email');
        $reminder1->setMinutes(1440);

        $reminder2 = new EventReminder();
        $reminder2->setMethod('email');
        $reminder2->setMinutes(2880);

        $reminder3 = new EventReminder();
        $reminder3->setMethod('popup');
        $reminder3->setMinutes(4320);

        $reminders = new EventReminders();
        $reminders->setUseDefault(false);
        $reminders->setOverrides([$reminder1, $reminder2, $reminder3]);

        $event->googleEvent->setReminders($reminders);

        $event->save();
    }
}
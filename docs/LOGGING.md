# Logging

To ensure a thorough logging during the processes
handled by this application we use Symfonys
[event dispatcher component](https://symfony.com/doc/current/components/event_dispatcher.html),
as this allows easy addition and modification of events and their handlers.

The logging is done on case level.
This means anything related to a case must be logged
whether it is editing a party, adding the case to an agenda or simply viewing it.

To store the logs in our database we make a LogEntry entity
with the necessary fields.

## Workflow

If adding a feature to the application that is to be logged:

* Implement feature
* Create event(s)
* Create or update event subscriber
* Dispatch events

We keep a folder for events and one for its subscribers.

```sh
/project_root
  /src
    /Event
      SomeAbstractEvent.php
      SomeSpecificEvent.php
    /EventSubscriber
      SomeEventSubsrciber.php
```

### Events

We have an abstract event class for each entity
that in any shape or form may relate to a case.

We then create specific event classes such as PartyUpdatedEvent
that extend the above mentioned abstract class.

#### Example Event

Abstract PartyEvent class:

```php
<?php

namespace App\Event;

use App\Entity\Party;
use Symfony\Contracts\EventDispatcher\Event;

abstract class PartyEvent extends Event
{
    private $party;

    public function __construct(Party $party)
    {
        $this->party = $party;
    }

    public function getParty(): Party
    {
        return $this->party;
    }
}
```

Example extension of above abstract class:

```php
<?php

namespace App\Event;

class PartyUpdatedEvent extends PartyEvent
{
}
```

### EventSubscriber

We will keep a subscriber for each type of events,
i.e. PartyEvent or BoardMemberEvent.

As an example, the PartyEventSubscriber will listen to
event classes that extends PartyEvent:

```php
<?php

namespace App\EventSubscriber;

use App\Entity\Party;
use App\Entity\LogEntry;
use App\Event\PartyUpdatedEvent;
...
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PartyEventSubscriber implements EventSubscriberInterface
{
    private $entityManager;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            PartyUpdatedEvent::class => 'onUpdated',
            ...
        ];
    }
    
    public function onUpdated(PartyUpdatedEvent $event)
    {
        // logging logic
    }
}
```

### Dispatching event

To dispatch an event simply inject the
[EventDispatcher](https://github.com/symfony/symfony/blob/5.2/src/Symfony/Component/EventDispatcher/EventDispatcher.php)
and create an instance of the event:

```php
$event = new PartyUpdatedEvent($party);
$dispatcher->dispatch($event, PartyUpdatedEvent::NAME);
```

## Useful links

* [Event and Event Listeners](https://symfony.com/doc/current/event_dispatcher.html)
* [EventDispatcher component](https://symfony.com/doc/current/components/event_dispatcher.html)

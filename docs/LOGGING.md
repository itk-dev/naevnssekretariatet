# Logging

To ensure a thorough logging during the processes
handled by this application we use
[doctrine events](https://www.doctrine-project.org/projects/doctrine-orm/en/2.8/reference/events.html).

This allows us to use already defined
[Lifecycle Events](https://www.doctrine-project.org/projects/doctrine-orm/en/2.8/reference/events.html#lifecycle-events),
and specifying our own logic when listening and handling such events.

Logging is done on case level.
In other words, anything related to a case must be logged
whether it is editing a party,
adding the case to an agenda or simply viewing the case.

To store the logs in our database we make a LogEntry entity
with the necessary fields.

## Workflow

If adding a feature to the application that contains logic
that must be logged:

* Implement feature
* Create entity listener and logging logic
* Add entity listener to entity if not already there

We keep a folder for entity listeners:

```sh
/project_root
  /src
    /Entity
      SomeEntity.php
    /EntityListener
      SomeEntityListener.php
```

### Events

The doctrine events we are interested in are

* `postRemove`
* `postUpdate`
* `postLoad`

We are not interested in `postPersist`, as creation of a
Party is not something we wish to log. Instead, the logging
occur when updating a case by adding a party to it.

All doctrine lifecycle events are listed
[here](https://www.doctrine-project.org/projects/doctrine-orm/en/2.8/reference/events.html#lifecycle-events).

### EventListener

We will keep a listener for each entity that in any shape or form
relates to a case, as any changes to such entity must be logged
on the respective case.

### Party entity example

The following is an example of how to log party updates:

#### Creating the EntityListener

```php
<?php

namespace App\EntityListener;

class PartyChangedListener extends AbstractChangedListener
{
    public function postUpdate(Party $party, LifecycleEventArgs $event)
    {
        // Obtain affected cases
        // Create LogEntry entity
        // Persist LogEntry to EntityManager
    }
    
    public function postRemove(Party $party, LifecycleEventArgs $event)
    {
        // Do something on post update.
    }
}
```

#### Adding the EntityListener

```php
<?php

namespace App\Entity;

/** @Entity @EntityListeners({"PartyListener"}) */
class Party
{
    // ....
}
```

## Useful links

Both links beneath have documentation on
Doctrine Events, Listeners and Subscribers.

* [Doctrine Documentation](https://www.doctrine-project.org/projects/doctrine-orm/en/2.8/reference/events.html)
* [Symfony Documentation](https://symfony.com/doc/current/doctrine/events.html)

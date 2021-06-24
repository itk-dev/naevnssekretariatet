# Logging

To ensure a thorough logging during the cases
handled by this application we use
[doctrine events](https://www.doctrine-project.org/projects/doctrine-orm/en/2.8/reference/events.html).
This allows us to use already defined
[Lifecycle Events](https://www.doctrine-project.org/projects/doctrine-orm/en/2.8/reference/events.html#lifecycle-events),
and specifying our own logic when listening and handling such events.

Logging is done on case level.
In other words, anything related to a case must be logged
whether it is editing a party,
adding the case to an agenda or simply viewing the case.

When logging eg. a Party change on a case we do not want
to log all changes, but rather changes to specific properties.
As an example, if a party is related to two cases, changes to
case one should not be logged for case two.

To help this, each entity that directly relates to cases must implement
`LoggableEntityInterface`, which ensures a `getLoggableProperties`
function. All loggable properties must have a getter.

For storing logs in our database we make a LogEntry entity
with the necessary fields. That is

* Case ID
* Entity
* Entity ID
* Action
* User
* Data
* Timestamp

The reason for the Entity and Entity ID columns are
to specify which entity has been modified e.g.
Party has been updated, with Data containing the updated data.

## Workflow

If adding an entity to the application that relates to cases
and therefore must be logged:

* Add entity and implement feature
* Create entity listener extending `AbstractRelatedToCaseListener`
* Listen to doctrine events, eg. `postUpdate`, in entity listener
  and call `logActivity($action, $args)`
* Add entity listener to entity

We keep the following structure:

```sh
/project_root
  /src
    /Entity
      SomeEntity.php
    /Logging
      /EntityListener
        AbstractEntityListener.php
        AbstractRelatedToCaseListener.php
        SomeEntityListener.php
      
```

### Events

The doctrine events we are interested in are

* `postRemove`
* `postUpdate`
* `postLoad`
* `postPersist`

All doctrine lifecycle events are listed
[here](https://www.doctrine-project.org/projects/doctrine-orm/en/2.8/reference/events.html#lifecycle-events).

### EventListener

We will keep a listener for each entity that in any shape or form
relates to a case, as any changes to such entity must be logged
on the respective case. Any such listener must extend
`AbstractRelatedToCaseListener`.

### Party entity example

The following is an example of how to log party updates:

#### Creating the PartyListener

```php
<?php

namespace App\EntityListener;

use App\Entity\Party;
use App\Logging\ItkDevLoggingException;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\ORMException;
use Symfony\Component\Security\Core\Security;

class PartyListener extends AbstractRelatedToCaseListener
{
    public function __construct(Security $security)
    {
        parent::__construct($security);
    }

    // Listening to doctrine events, here postUpdate
    
    /**
     * @throws ItkDevLoggingException
     * @throws ORMException
     */
    public function postUpdate(Party $party, LifecycleEventArgs $args)
    {
        $this->logActivity('Update', $args);
    }
}
```

#### Modifying the Party entity

```php
<?php

namespace App\Entity;

/**
 * ..
 * @ORM\EntityListeners({"App\Logging\EntityListener\PartyListener"})
 * ..
 */
class Party implements LoggableEntityInterface
{
    // ....
    
    public function getLoggableProperties(): array
    {
        return [
            'id',
            'name',
            '...',
        ];
    }
}
```

## Useful links

Both links beneath have documentation on
Doctrine Events &  Listeners.

* [Doctrine Documentation](https://www.doctrine-project.org/projects/doctrine-orm/en/2.8/reference/events.html)
* [Symfony Documentation](https://symfony.com/doc/current/doctrine/events.html)

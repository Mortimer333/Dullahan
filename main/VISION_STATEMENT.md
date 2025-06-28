# Vision statement

System should be made of domains each represented by set of relatable functionality (Assets, Objects, Thumbnails
etc.), by estimated size, by complexity of integration in already existing module and possibility of disregarding 
(possibility of user not using one of the functionality in the module).

> As an example you can take Assets and Thumbnails. Each very similar topics but not everybody who uses centralized
> asset management will be creating thumbnails. Thumbnail is big enough set of functionality that even if related
> to Assets by topic it might be too much of hassle to integrate it completely with them.

Each module should be framework-agnostic with Symfony as a first integration candidate. Create them to work at least for 
Symfony and Laravel with Symfony as the first bridge implementation. 

## Current architecture best candidate:

1. Start with layered architecture from DDD: Infrastructure <- Domain <- Application (thin) <- Presentation.
2. Then create folder Ports and add it again inside of it. 
> For the starting module it is okay to also add folder Adapters with folder named after the Adapters target
> and add another set of DDD LA folders. But in the end it will have to go into separate package called something like
> dullahan-asset-bridge-symfony

You should end up with something similar to this:
```
Ports
 - Presentation
 - Application
 - Domain
 - Infrastructure
Adapter     <--- Optional, must by seperated into package eventually
 - Symfony
 - - Presentation
 - - Application
 - - Domain
 - - Infrastructure
Presentation
Application
Domain
Infrastructure
```

As I doubt that DDD LA requires explanation but to iterate:
- Layer at the top has access to anything below: 
  - Presentation can access anything
  - Application can access self, Domain and Infrastructure
  - Domain can access self and Infrastructure
  - Infrastructure can only access self
- Presentation is where all user access points are stored - HTTP, Events, Command etc.
- Application should be as small as possible, it should only work as a Facade for services in Domain. Think of it as
creating set of instructions under an action.
- Domain holds all functionality related to the module
- Infrastructure is where all communication with outside tools is set - database, message queues, integrations with 3rd 
party

Of course this is indefinitely more complex but for starters should be enough.

Now to add the twist in form of framework-agnostic approach - we are using Ports and Adapters for this purpose.
Any framework functionality related finds its place in Adapter folder and gets abstraction in form of Interface from 
Ports folder.

### How to use Ports and Adapters?
If you want to create, for example, Repository for you Entity you need to first create interface for actions possible 
on it and put them in Infrastructure folder in Ports. Only then you can create it using framework related libraries 
(like Doctrine) and add required methods by the Interface. Voilà, you've just created polymorphic abstraction which can 
be switched with other implementations (like Eloquent).

In the end it should look like this:
```
Ports
 - Infrastructure
 - - MyRepositoryInterface.php
Adapter     <--- Optional, must by seperated into package eventually
 - Symfony
 - - Infrastructure
 - - - MyRepositoryDoctrinImpl.php
 - Laravel
 - - Infrastructure
 - - - MyRepositoryEloquentImpl.php
```

### Layers accessibility?

Ports can be accessed by any other layer if the order is correct. For example the root/Presentation can access 
root/Ports/Domain but root/Domain cannot access root/Ports/Presentation. So we are keeping the boundaries of normal LA
but in another dimension.

Situation changes a little for accessing Adapters. Adapters can access anything on the same level or lower but Ports 
and root implementation do not access anything in Adapters. This will end up as another package we won't
be using, so it makes sens that we won't be importing from it.

### Good rule of thumb

If there is an import of framework related libraries like `Symfony\Component\HttpFoundation\Request` in your root or 
Ports directory something is wrong and you have to abstract.

### Keep all interfaces in the Ports at top level

Ports is a place where all interfaces go - quite obvious for numerous reasons like ability to freely change application 
architecture outside of Ports. To make is as simple as possible all interfaces in ports should be at top level 
(in Presentation, Application, Domain or Infrastructure layer) without any nested folders. This way we can keep this part
of the application simple and don't decide how should the implementation be structured.

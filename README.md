# EBEmailBundle

## Minimum configuration

Just add your senders (only one is required actually) :

    eb_email:
        senders:
            -
                name: John DOE
                email: john.doe@gmail.com
        emails: []

## Configure your emails

We have to send a ``contact`` email with a specific subject and body.

    eb_email:
        # ...
        emails:
            contact:
                subject: 'New contact !'
                text_template: 'AppBundle:Email:_contact.txt.twig'

If you want to send an email with an alternative ``text/plain``
and ``text/html`` just use the ``html_template`` key :

    eb_email:
        # ...
        emails:
            contact:
                subject: 'New contact !'
                text_template: 'AppBundle:Email:_contact.txt.twig'
                html_template: 'AppBundle:Email:_contact.html.twig'

You can use ``twig`` syntax in your subject :

    eb_email:
        # ...
        emails:
            contact:
                subject: 'New contact from {{contact.name}} !'
                # ...

## Send your email

    $this->get('eb_email')->send('contact', 'john.doe@gmail.com');

With 2 recipients :

    $this->get('eb_email')->send('contact', [
        'john.doe1@gmail.com',
        'john.doe2@gmail.com',
    ]);

With a user object implementing a ``getUsername`` method :

    $this->get('eb_email')->send('contact', $user);

With a templating ``message`` var :

    $this->get('eb_email')->send('contact', 'john.doe1@gmail.com', [
        'message' => $message,
    ]);

With an ``header`` image ``<img src="{{ header }}"/>`` :

    $this->get('eb_email')->send('contact', 'john.doe1@gmail.com', [
        'message' => $message,
    ], [
        'header' => '/var/header.png',
    ]);

With a PDF attachment :

    $this->get('eb_email')->send('contact', 'john.doe1@gmail.com', [
        'message' => $message,
    ], [], [
        '/var/attachment.pdf',
    ]);

## Configure globals, images, attachments and recipients for all mails

Add ``app_name`` global variable to all emails :

    eb_email:
        # ...
        globals:
            app_name: BundleDemo

Add ``header`` and ``footer`` images to all emails (useful for a twig layout) :

    eb_email:
        # ...
        images:
            header: /var/header.png
            footer: /var/footer.png

Add some PDF to all emails :

    eb_email:
        # ...
        attachments:
            - /var/terms.pdf

Add recipients to all emails :

    eb_email:
        # ...
        recipients:
            -
                name: AppName
                email: contact@appname.com

## Configure images and attachments for one email

Add the ``map`` image to the ``contact`` email :

    eb_email:
        # ...
        emails:
            contact:
                # ...
                images:
                    map: /var/map.png

Add one PDF to the ``contact`` email :

    eb_email:
        # ...
        emails:
            contact:
                # ...
                attachments:
                    - /var/satisfaction.pdf

Add one default recipient to the ``contact`` email :

    eb_email:
        # ...
        emails:
            contact:
                # ...
                recipients:
                    -
                        name: AppName
                        email: contact@appname.com

## Full configuration

    # Default configuration for "EBEmailBundle"
    eb_email:
        senders:              # Required

            # Sender name
            name:                 ~ # Required, Example: John Doe

            # Sender email
            email:                ~ # Required, Example: john.doe@gmail.com

        # Global to add in each template
        globals:              # Example: A parameter

            # Prototype
            name:                 ~

        # Images to attach inline
        images:               # Example: /path/to/file

            # Prototype
            name:                 ~

        # Files to attach
        attachments:          [] # Example: /path/to/file
        recipients:

            # Recipient name
            name:                 ~ # Required, Example: John Doe

            # Recipient email
            email:                ~ # Required, Example: john.doe@gmail.com
        emails:

            # Prototype
            name:

                # Text Template
                text_template:        ~ # Required, Example: AcmeDefautBundle::_text_email.html.twig

                # HTML Template
                html_template:        null # Example: AcmeDefautBundle::_html_email.html.twig

                # Email subject
                subject:              ~ # Required, Example: Welcome {{user.username}} to {{app_name}} !

                # Global to add in this template
                globals:              # Example: A parameter

                    # Prototype
                    name:                 ~

                # Images to attach inline
                images:               # Example: /path/to/file

                    # Prototype
                    name:                 ~

                # Files to attach
                attachments:          [] # Example: /path/to/file
                recipients:

                    # Recipient name
                    name:                 ~ # Required, Example: John Doe

                    # Recipient email
                    email:                ~ # Required, Example: john.doe@gmail.com

INSERT INTO `mail_templates`
    (`event_type`, `subject`, `from_mail`, `from_name`, `body_html`, `body_text`, `active`)
VALUES
(   'loan_confirm',
    'Leen bevestiging voor: :book_name',
    ':from_mail',
    ':from_name',
    '<body>
        <div class="header-content">
            <h2 class="header-text">Hallo :user_name.</h2>
        </div>
        <div class="body-content">
            <p class="body-text-1">
                Bedankt voor het lenen van <strong>:book_name</strong>, we hopen dat het boek je gaat bevallen.
                Als de leenperiode bijna is verstreken, krijg je nog een herrinering van ons.
            </p>
            <p class="body-text-2">
                Het einde van de huidige leenperiode is: <strong>:due_date</strong>.
            </p>
        </div>
    </body>
    <footer class="footer-content">
        <p class="footer-goodbye">Mvg & Bvd, <br> De Aletho Bibliotheek.</p><hr>
    </footer>',
    'Hallo :user_name. Bedankt voor het lenen van :book_name. Het einde van de huidige leenperiode is: :due_date. Mvg & Bvd, De Aletho Bibliotheek.',
    1
),
(   'pickup_ready_confirm',
    'Het boek: :book_naam is klaar voor ophalen',
    ':from_mail',
    ':from_name',
    '<body>
        <div class="header-content">
            <h2 class="header-text">Hallo :user_name.</h2>
        </div>
        <div class="body-content">
            <p class="body-text-1">
                Bedankt voor het lenen van <strong>:book_name</strong>, we hopen dat het boek je gaat bevallen.
                Het boek is aangekomen bij <strong>:office</strong>, en kan worden opgehaald.
            </p>
            <p class="body-text-2">
                De leenperiode gaat pas van start, als u het boek heeft opgehaald.
            </p>
        </div>
    </body>
    <footer class="footer-content">
        <p class="footer-goodbye">Mvg & Bvd, <br> De Aletho Bibliotheek.</p><hr>
    </footer>',
    'Hallo :user_name. Bedankt voor het lenen van <strong>:book_name</strong>, we hopen dat het boek je gaat bevallen.Het boek is aangekomen bij <strong>:office</strong>, en kan worden opgehaald. De leenperiode gaat pas van start, als u het boek heeft opgehaald. Mvg & Bvd, De Aletho Bibliotheek.',
    1
),
(   'pickup_confirm',
    'Lening eind datum voor: :book_name',
    ':from_mail',
    ':from_name',
    '<body>
        <div class="header-content">
            <h2 class="header-text">Hallo :user_name.</h2>
        </div>
        <div class="body-content">
            <p class="body-text-1">
                Bedankt voor het ophalen van <strong>:book_name</strong>, we hopen dat het boek je gaat bevallen.
            </p>
            <p class="body-text-2">
                De leenperiode is gestart, en het einde van de huidige leenperiode is: <strong>:due_date</strong>.
            </p>
        </div>
    </body>
    <footer class="footer-content">
        <p class="footer-goodbye">Mvg & Bvd, <br> De Aletho Bibliotheek.</p><hr>
    </footer>',
    'Hallo :user_name. Bedankt voor het ophalen van :book_name. De leenperiode is gestart, en het einde van de huidige leenperiode is: :due_date. Mvg & Bvd, De Aletho Bibliotheek.',
    1
),
(   'return_reminder',
    'Verloop reminder voor: :book_name',
    ':from_mail',
    ':from_name',
    '<body>
        <div class="header-content">
            <h2 class="header-text">Hallo :user_name.</h2>
        </div>
        <div class="body-content">
            <p class="body-text-1">
                De leen periode van: <strong>:book_name</strong>, is bijna verlopen.
                Vergeet niet om het boek op tijd terug te brengen naar de bibliotheek.
            </p>
            <p class="body-text-2">
                Het einde van de huidige leenperiode is: <strong>:due_date</strong>.
            </p>
        </div>
        :action_block
    </body>
    <footer class="footer-content">
        <p class="footer-goodbye">Mvg & Bvd, <br> De Aletho Bibliotheek.</p><hr>
    </footer>',
    'Hallo :user_name. De leen periode van: :book_name, is bijna verlopen. Vergeet niet om het boek op tijd terug te brengen naar de bibliotheek. Het einde van de huidige leenperiode is: :due_date. :action_block Mvg & Bvd, De Aletho Bibliotheek.',
    1
),
(   'transport_request',
    'Transport verzoek voor: :book_name',
    ':from_mail',
    ':from_name',
    '<body>
        <div class="header-content">
            <h2 class="header-text">Hallo :user_name.</h2>
        </div>
        <div class="body-content">
            <p class="body-text-1">
                Er is een transport verzoek voor: <strong>:book_name</strong>.
                Zou je het boek mee willen nemen naar: <strong>:office</strong>
            </p>
        </div>
    </body>
    <footer class="footer-content">
        <p class="footer-goodbye">Mvg & Bvd, <br> De Aletho Bibliotheek.</p><hr>
    </footer>',
    'Hallo :user_name. Er is een transport verzoek voor: :book_name. Zou je het boek mee willen nemen naar: :office. Mvg & Bvd, De Aletho Bibliotheek.',
    1
),
(   'reserv_confirm',
    'Reservering bevestiging voor: :book_name',
    ':from_mail',
    ':from_name',
    '<body>
        <div class="header-content">
            <h2 class="header-text">Hallo :user_name.</h2>
        </div>
        <div class="body-content">
            <p class="body-text-1">
                Bedankt voor het Reserveren van <strong>:book_name</strong>, we hopen dat het boek je gaat bevallen.
                Als de leenperiode bijna is verstreken, krijg je nog een herrinering van ons.
            </p>
            <p class="body-text-2">
                Het einde van de huidige leenperiode is: <strong>:due_date</strong>.
            </p>
        </div>
    </body>
    <footer class="footer-content">
        <p class="footer-goodbye">Mvg & Bvd, <br> De Aletho Bibliotheek.</p><hr>
    </footer>',
    'Hallo :user_name. Bedankt voor het reserveren van :book_name. Het einde van de huidige leenperiode is: :due_date. Mvg & Bvd, De Aletho Bibliotheek.',
    1
),
(   'overdue_notice',
    'Verstreken lening voor: :book_name',
    ':from_mail',
    ':from_name',
    '<body>
        <div class="header-content">
            <h2 class="header-text">Hallo :user_name.</h2>
        </div>
        <div class="body-content">
            <p class="body-text-1">
                Het is ons opgevallen dat de lening van <strong>:book_name</strong> is verstreken.
                Breng het boek zo snel mogelijk terug naar de bibliotheek, zodat andere het boek ook kunnen lenen.
            </p>
        </div>
    </body>
    <footer class="footer-content">
        <p class="footer-goodbye">Mvg & Bvd, <br> De Aletho Bibliotheek.</p><hr>
    </footer>',
    'Hallo :user_name.Het is ons opgevallen dat de lening van <strong>:book_name</strong> is verstreken.Breng het boek zo snel mogelijk terug naar de bibliotheek, zodat andere het boek ook kunnen lenen. Mvg & Bvd, De Aletho Bibliotheek.',
    1
);
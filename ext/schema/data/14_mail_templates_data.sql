INSERT INTO `mail_templates`
    (`event_type`, `subject`, `from_mail`, `from_name`, `body_html`, `body_text`, `active`)
VALUES
(   'loan_confirm',
    'Leen bevestiging voor: :book_name',
    ':from_mail',
    ':from_name',
    '<tr>
        <td style="background-color:rgb(28, 50, 105); color:#ffffff; padding:16px; border-top-left-radius:6px; border-top-right-radius:6px; text-align:center;">
            <h2 style="margin:0; font-family:'BC Alphapipe', Arial, sans-serif;">Hallo :user_name,</h2>
        </td>
    </tr>
    <tr>
        <td style="padding:20px; font-family:'Quicksand', Arial, sans-serif; color:#333; text-align:center;">
            <p style="margin:0 0 16px;">
            Bedankt voor het lenen van:<br><strong>:book_name</strong><br><br>
            We hopen dat het boek je gaat bevallen.<br><br>
            Als de leenperiode bijna is verstreken, krijg je nog een herinnering van ons.
            </p>
            <p style="margin:0;">
            Het einde van de huidige leenperiode is:<br><strong>:due_date</strong>.
            </p>
            :action_block
        </td>
    </tr>
    <tr>
        <td style="background-color:rgb(28, 50, 105); color:#ffffff; padding:16px; border-bottom-left-radius:6px; border-bottom-right-radius:6px; text-align:center;">
            <p style="margin:0; font-family:'Quicksand', Arial, sans-serif;">
            Mvg & Bvd,<br>De Aletho Bibliotheek.
            </p>
        </td>
    </tr>',
    'Hallo :user_name. Bedankt voor het lenen van :book_name. Het einde van de huidige leenperiode is: :due_date. Mvg & Bvd, De Aletho Bibliotheek.',
    1
),
(   'pickup_ready_confirm',
    'Het boek: :book_naam is klaar voor ophalen',
    ':from_mail',
    ':from_name',
    '<body>
        <div class="header-content">
            <h2 class="titel-teks">Hallo :user_name.</h2>
        </div>
        <div class="body-content">
            <p class="tussenkoppen-tekst">
                Bedankt voor het lenen van <strong>:book_name</strong>, we hopen dat het boek je gaat bevallen.
                Het boek is aangekomen bij <strong>:office</strong>, en kan worden opgehaald.
            </p>
            <p class="tussenkoppen-tekst">
                De leenperiode gaat pas van start, als u het boek heeft opgehaald.
            </p>
        </div>
    </body>
    <footer class="footer-content">
        <p class="overige-tekst">Mvg & Bvd, <br> De Aletho Bibliotheek.</p><hr>
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
            <h2 class="titel-tekst">Hallo :user_name.</h2>
        </div>
        <div class="body-content">
            <p class="tussenkoppen-tekst">
                Bedankt voor het ophalen van <strong>:book_name</strong>, we hopen dat het boek je gaat bevallen.
            </p>
            <p class="tussenkoppen-tekst">
                De leenperiode is gestart, en het einde van de huidige leenperiode is: <strong>:due_date</strong>.
            </p>
        </div>
    </body>
    <footer class="footer-content">
        <p class="overige-tekst">Mvg & Bvd, <br> De Aletho Bibliotheek.</p><hr>
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
            <h2 class="titel-tekst">Hallo :user_name.</h2>
        </div>
        <div class="body-content">
            <p class="tussenkoppen-tekst">
                De leen periode van: <strong>:book_name</strong>, is bijna verlopen.
                Vergeet niet om het boek op tijd terug te brengen naar de bibliotheek.
            </p>
            <p class="tussenkoppen-tekst">
                Het einde van de huidige leenperiode is: <strong>:due_date</strong>.
            </p>
        </div>
        :action_block
    </body>
    <footer class="footer-content">
        <p class="overige-tekst">Mvg & Bvd, <br> De Aletho Bibliotheek.</p><hr>
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
            <h2 class="titel-tekst">Hallo :user_name.</h2>
        </div>
        <div class="body-content">
            <p class="tussenkoppen-tekst">
                Er is een transport verzoek voor: <strong>:book_name</strong>.
                Zou je het boek mee willen nemen naar: <strong>:office</strong>
            </p>
        </div>
    </body>
    <footer class="footer-content">
        <p class="overige-tekst">Mvg & Bvd, <br> De Aletho Bibliotheek.</p><hr>
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
            <h2 class="titel-tekst">Hallo :user_name.</h2>
        </div>
        <div class="body-content">
            <p class="tussenkoppen-tekst">
                Bedankt voor het Reserveren van <strong>:book_name</strong>, we hopen dat het boek je gaat bevallen.
                Als de leenperiode bijna is verstreken, krijg je nog een herrinering van ons.
            </p>
            <p class="tussenkoppen-tekst">
                Het einde van de huidige leenperiode is: <strong>:due_date</strong>.
            </p>
        </div>
    </body>
    <footer class="footer-content">
        <p class="overige-tekst">Mvg & Bvd, <br> De Aletho Bibliotheek.</p><hr>
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
            <h2 class="titel-tekst">Hallo :user_name.</h2>
        </div>
        <div class="body-content">
            <p class="tussenkoppen-tekst">
                Het is ons opgevallen dat de lening van <strong>:book_name</strong> is verstreken.
                Breng het boek zo snel mogelijk terug naar de bibliotheek, zodat andere het boek ook kunnen lenen.
            </p>
        </div>
    </body>
    <footer class="footer-content">
        <p class="overige-tekst">Mvg & Bvd, <br> De Aletho Bibliotheek.</p><hr>
    </footer>',
    'Hallo :user_name.Het is ons opgevallen dat de lening van <strong>:book_name</strong> is verstreken.Breng het boek zo snel mogelijk terug naar de bibliotheek, zodat andere het boek ook kunnen lenen. Mvg & Bvd, De Aletho Bibliotheek.',
    1
);
INSERT INTO `mail_templates`
    (`templ_subject`, `templ_body_html`, `templ_body_text`)
VALUES
-- loan_confirm
(   'Leen bevestiging voor: :book_name',
    '<tr>
        <td style="background-color:rgb(28, 50, 105); color:#ffffff; padding:16px; border-top-left-radius:6px; border-top-right-radius:6px; text-align:center;">
            <h2 style="margin:0; font-family:\"BC Alphapipe"\, Arial, sans-serif;">Hallo :loaner_name,</h2>
        </td>
    </tr>
    <tr>
        <td style="padding:20px; font-family:\"Quicksand"\, Arial, sans-serif; color:#333; text-align:center;">
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
            <p style="margin:0; font-family:\"Quicksand"\, Arial, sans-serif;">
            Mvg & Bvd,<br>De Aletho Bibliotheek.
            </p>
        </td>
    </tr>',
    'Hallo :loaner_name,\r\n
    \r\n
    Bedankt voor het lenen van:\r\n
    :book_name\r\n
    \r\n
    We hopen dat het boek je gaat bevallen.\r\n
    \r\n
    Als de leenperiode bijna is verstreken, krijg je nog een herinnering van ons.\r\n
    \r\n
    Het einde van de huidige leenperiode is:\r\n
    :due_date\r\n
    \r\n
    :action_block\r\n
    \r\n
    ---\r\n
    Mvg & Bvd,\r\n
    De Aletho Bibliotheek\r\n'
),

-- pickup_ready_confirm
(   'Het boek: :book_name is klaar voor ophalen',
    '<tr>
        <td style="background-color:rgb(28, 50, 105); color:#ffffff; padding:16px; border-top-left-radius:6px; border-top-right-radius:6px; text-align:center;">
            <h2 style="margin:0; font-family:\"BC Alphapipe"\, Arial, sans-serif;">Hallo :loaner_name,</h2>
        </td>
    </tr>
    <tr>
        <td style="padding:20px; font-family:\"Quicksand"\, Arial, sans-serif; color:#333; text-align:center;">
            <p style="margin:0 0 16px;">
                Bedankt voor het lenen van:<br><strong>:book_name</strong><br><br>
                We hopen dat het boek je gaat bevallen.<br><br>
                Het boek is aangekomen in <strong>:office</strong>, en kan worden opgehaald.
            </p>
            <p style="margin:0 0 16px;">
                De leenperiode gaat pas van start, als u het boek heeft opgehaald.
            </p>
            :action_block
        </td>
    </tr>
    <tr>
        <td style="background-color:rgb(28, 50, 105); color:#ffffff; padding:16px; border-bottom-left-radius:6px; border-bottom-right-radius:6px; text-align:center;">
            <p style="margin:0; font-family:\"Quicksand"\, Arial, sans-serif;">
                Mvg & Bvd,<br>De Aletho Bibliotheek.
            </p>
        </td>
    </tr>',
    'Hallo :loaner_name,\r\n
    \r\n
    Bedankt voor het lenen van:\r\n
    :book_name\r\n
    \r\n
    We hopen dat het boek je gaat bevallen.\r\n
    \r\n
    Het boek is aangekomen in :office, en kan worden opgehaald.\r\n
    \r\n
    De leenperiode gaat pas van start, als u het boek heeft opgehaald.\r\n
    \r\n
    :action_block\r\n
    \r\n
    ---\r\n
    Mvg & Bvd,\r\n
    De Aletho Bibliotheek\r\n'
),

-- pickup_confirm
(   'Lening eind datum voor: :book_name',
    '<tr>
        <td style="background-color:rgb(28, 50, 105); color:#ffffff; padding:16px; border-top-left-radius:6px; border-top-right-radius:6px; text-align:center;">
            <h2 style="margin:0; font-family:\"BC Alphapipe"\, Arial, sans-serif;">Hallo :loaner_name,</h2>
        </td>
    </tr>
    <tr>
        <td style="padding:20px; font-family:\"Quicksand"\, Arial, sans-serif; color:#333; text-align:center;">
            <p style="margin:0 0 16px;">
                Bedankt voor het ophalen van <strong>:book_name</strong>, we hopen dat het boek je gaat bevallen.
            </p>
            <p style="margin:0;">
                De leenperiode is gestart, en het einde van de huidige leenperiode is:<br><strong>:due_date</strong>.
            </p>
            :action_block
        </td>
    </tr>
    <tr>
        <td style="background-color:rgb(28, 50, 105); color:#ffffff; padding:16px; border-bottom-left-radius:6px; border-bottom-right-radius:6px; text-align:center;">
            <p style="margin:0; font-family:\"Quicksand"\, Arial, sans-serif;">
                Mvg & Bvd,<br>De Aletho Bibliotheek.
            </p>
        </td>
    </tr>',
    'Hallo :loaner_name,\r\n
    \r\n
     Bedankt voor het ophalen van:\r\n
    :book_name\r\n
    \r\n
    We hopen dat het boek je gaat bevallen.\r\n
    \r\n
    Het boek is aangekomen in :office, en kan worden opgehaald.\r\n
    \r\n
    De leenperiode is gestart, en het einde van de huidige leenperiode is:\r\n
    :due_date\r\n
    \r\n
    :action_block\r\n
    \r\n
    ---\r\n
    Mvg & Bvd,\r\n
    De Aletho Bibliotheek\r\n'
),

-- return_reminder
(   'Verloop reminder voor: :book_name',
    '<tr>
        <td style="background-color:rgb(28, 50, 105); color:#ffffff; padding:16px; border-top-left-radius:6px; border-top-right-radius:6px; text-align:center;">
            <h2 style="margin:0; font-family:\"BC Alphapipe"\, Arial, sans-serif;">Hallo :loaner_name,</h2>
        </td>
    </tr>
    <tr>
        <td style="padding:20px; font-family:\"Quicksand"\, Arial, sans-serif; color:#333; text-align:center;">
            <p style="margin:0 0 16px;">
                De leenperiode van: <strong>:book_name</strong> is bijna verlopen.<br>
                Vergeet niet om het boek op tijd terug te brengen naar de bibliotheek.
            </p>
            <p style="margin:0;">
                Het einde van de huidige leenperiode is:<br><strong>:due_date</strong>.
            </p>
            :action_block
        </td>
    </tr>
    <tr>
        <td style="background-color:rgb(28, 50, 105); color:#ffffff; padding:16px; border-bottom-left-radius:6px; border-bottom-right-radius:6px; text-align:center;">
            <p style="margin:0; font-family:\"Quicksand"\, Arial, sans-serif;">
                Mvg & Bvd,<br>De Aletho Bibliotheek.
            </p>
        </td>
    </tr>',
    'Hallo :loaner_name,\r\n
    \r\n
    De leenperiode van: :book_name is bijna verlopen.\r\n
    Vergeet niet om het boek op tijd terug te brengen naar de bibliotheek.\r\n
    \r\n
    Het einde van de huidige leenperiode is:\r\n
    :due_date\r\n
    \r\n
    :action_block\r\n
    \r\n
    ---\r\n
    Mvg & Bvd,\r\n
    De Aletho Bibliotheek\r\n'
),

-- transport_request
(   'Transport verzoek voor: :book_name',
    '<tr>
        <td style="background-color:rgb(28, 50, 105); color:#ffffff; padding:16px; border-top-left-radius:6px; border-top-right-radius:6px; text-align:center;">
            <h2 style="margin:0; font-family:\"BC Alphapipe"\, Arial, sans-serif;">Hallo :admin_name,</h2>
        </td>
    </tr>
    <tr>
        <td style="padding:20px; font-family:\"Quicksand"\, Arial, sans-serif; color:#333; text-align:center;">
            <p style="margin:0;">
                Er is een transport verzoek voor: <strong>:book_name</strong>.<br>
                Voor de lener: <strong>:loaner_name</strong>.<br>
                Zou je het boek mee willen nemen naar: <strong>:office</strong>.
            </p>
            :action_block
        </td>
    </tr>
    <tr>
        <td style="background-color:rgb(28, 50, 105); color:#ffffff; padding:16px; border-bottom-left-radius:6px; border-bottom-right-radius:6px; text-align:center;">
            <p style="margin:0; font-family:\"Quicksand"\, Arial, sans-serif;">
                Mvg & Bvd,<br>De Aletho Bibliotheek.
            </p>
        </td>
    </tr>',
    'Hallo :admin_name,\r\n
    \r\n
    Er is een transportverzoek voor: :book_name\r\n
    Voor de lener: :loaner_name\r\n
    Zou je het boek mee willen nemen naar:\r\n
    :office\r\n
    \r\n
    :action_block\r\n
    \r\n
    ---\r\n
    Mvg & Bvd,\r\n
    De Aletho Bibliotheek\r\n'
),

-- reserv_confirm
(   'Reservering bevestiging voor: :book_name',
    '<tr>
        <td style="background-color:rgb(28, 50, 105); color:#ffffff; padding:16px; border-top-left-radius:6px; border-top-right-radius:6px; text-align:center;">
            <h2 style="margin:0; font-family:\"BC Alphapipe"\, Arial, sans-serif;">Hallo :loaner_name,</h2>
        </td>
    </tr>
    <tr>
        <td style="padding:20px; font-family:\"Quicksand"\, Arial, sans-serif; color:#333; text-align:center;">
            <p style="margin:0 0 16px;">
                Bedankt voor het reserveren van <strong>:book_name</strong>, we hopen dat het boek je gaat bevallen.<br><br>
            </p>
            <p style="margin:0 0 16px;">
                Als het boek klaar is om opgehaald te worden, krijg je nog een bericht van ons.<br>
                De leenperiode gaat pas van start, als u het boek heeft opgehaald.
            </p>
            :action_block
        </td>
    </tr>
    <tr>
        <td style="background-color:rgb(28, 50, 105); color:#ffffff; padding:16px; border-bottom-left-radius:6px; border-bottom-right-radius:6px; text-align:center;">
            <p style="margin:0; font-family:\"Quicksand"\, Arial, sans-serif;">
                Mvg & Bvd,<br>De Aletho Bibliotheek.
            </p>
        </td>
    </tr>',
    'Hallo :loaner_name,\r\n
    \r\n
    Bedankt voor het reserveren van:\r\n
    :book_name\r\n
    \r\n
    We hopen dat het boek je gaat bevallen.\r\n
    \r\n
    Als het boek klaar is om opgehaald te worden, krijg je nog een bericht van ons.\r\n
    \r\n
    De leenperiode gaat pas van start, als u het boek heeft opgehaald.\r\n
    \r\n
    :action_block\r\n
    \r\n
    ---\r\n
    Mvg & Bvd,\r\n
    De Aletho Bibliotheek\r\n'
),

-- overdue_reminder
(   'Verstreken lening voor: :book_name',
    '<tr>
        <td style="background-color:rgb(28, 50, 105); color:#ffffff; padding:16px; border-top-left-radius:6px; border-top-right-radius:6px; text-align:center;">
            <h2 style="margin:0; font-family:\"BC Alphapipe"\, Arial, sans-serif;">Hallo :loaner_name,</h2>
        </td>
    </tr>
    <tr>
        <td style="padding:20px; font-family:\"Quicksand"\, Arial, sans-serif; color:#333; text-align:center;">
            <p style="margin:0;">
                Het is ons opgevallen dat de lening van <strong>:book_name</strong> is verstreken.<br>
                Breng het boek zo snel mogelijk terug naar de bibliotheek, zodat anderen het boek ook kunnen lenen.
            </p>
            :action_block
        </td>
    </tr>
    <tr>
        <td style="background-color:rgb(28, 50, 105); color:#ffffff; padding:16px; border-bottom-left-radius:6px; border-bottom-right-radius:6px; text-align:center;">
            <p style="margin:0; font-family:\"Quicksand"\, Arial, sans-serif;">
                Mvg & Bvd,<br>De Aletho Bibliotheek.
            </p>
        </td>
    </tr>',
    'Hallo :loaner_name,\r\n
    \r\n
    Het is ons opgevallen dat de lening van:\r\n
    :book_name\r\n
    \r\n
    is verstreken.\r\n
    Breng het boek zo snel mogelijk terug naar de bibliotheek,\r\n
    zodat anderen het boek ook kunnen lenen.\r\n
    \r\n
    :action_block\r\n
    \r\n
    ---\r\n
    Mvg & Bvd,\r\n
    De Aletho Bibliotheek\r\n'
),

-- overdue_reminder_admin
(   'Verstreken lening voor: :book_name',
    '<tr>
        <td style="background-color:rgb(28, 50, 105); color:#ffffff; padding:16px; border-top-left-radius:6px; border-top-right-radius:6px; text-align:center;">
            <h2 style="margin:0; font-family:\"BC Alphapipe"\, Arial, sans-serif;">Hallo :admin_name,</h2>
        </td>
    </tr>
    <tr>
        <td style="padding:20px; font-family:\"Quicksand"\, Arial, sans-serif; color:#333; text-align:center;">
            <p style="margin:0;">
                De lener: <strong>:loaner_name</strong>, heeft het boek: <strong>:book_name</strong> nog niet ingeleverd.
                Er is een mail verzonden om de lener op de hoogte te stellen, maar checken of die is gelezen kan verstandig zijn.
            </p>
            :action_block
        </td>
    </tr>
    <tr>
        <td style="background-color:rgb(28, 50, 105); color:#ffffff; padding:16px; border-bottom-left-radius:6px; border-bottom-right-radius:6px; text-align:center;">
            <p style="margin:0; font-family:\"Quicksand"\, Arial, sans-serif;">
                Mvg & Bvd,<br>De Aletho Bibliotheek.
            </p>
        </td>
    </tr>',
    'Hallo :admin_name,\r\n
    \r\n
    De lener: :loaner_name, heeft het boek: :book_name nog niet ingeleverd.\r\n
    \r\n
    Er is een mail verzonden om de lener op de hoogte te stellen, maar checken of die is gelezen kan verstandig zijn.\r\n
    \r\n
    :action_block\r\n
    \r\n
    ---\r\n
    Mvg & Bvd,\r\n
    De Aletho Bibliotheek\r\n'
);
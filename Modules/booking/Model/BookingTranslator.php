<?php

/**
 * Class to translate the syggrif views
 * 
 * @author sprigent
 *
 */
class BookingTranslator {

    public static function booking($lang) {
        if ($lang == "fr") {
            return "Calendrier";
        }
        return "booking";
    }

    public static function bookingConfigAbstract($lang) {
        if ($lang == "fr") {
            return "Le module Booking permet de ...";
        }
        return "The Booking module allows to ...";
    }

    public static function configuration($lang = "") {
        if ($lang == "fr") {
            return "Configuration de \"Booking\"";
        }
        return "Booking configuration";
    }

    public static function Install_Repair_database($lang = "") {
        if ($lang == "fr") {
            return "Installer/Réparer la base de données";
        }
        return "Install/Repair database";
    }

    public static function Install_Txt($lang = "") {
        if ($lang == "fr") {
            return "Cliquer sur \"Installer\" pour installer ou réparer la base de données de 'Booking'.
					Cela créera les tables qui n'existent pas";
        }
        return "To repair the Booking mudule, click \"Install\". This will create the
				Booking tables in the database if they don't exists ";
    }

    public static function Activate_desactivate_menus($lang = "") {
        if ($lang == "fr") {
            return "Activer/désactiver les menus";
        }
        return "Activate/deactivate menus";
    }

    public static function Booking_settings($lang) {
        if ($lang == "fr") {
            return "Réglages calendrier";
        }
        return "Booking settings";
    }

    public static function Scheduling($lang) {
        if ($lang == "fr") {
            return "Horaires";
        }
        return "Scheduling";
    }

    public static function Packages($lang) {
        if ($lang == "fr") {
            return "Forfaits";
        }
        return "Packages";
    }

    public static function Supplementaries($lang) {
        if ($lang == "fr") {
            return "Suppléments";
        }
        return "Supplementaries";
    }

    public static function Quantities($lang) {
        if ($lang == "fr") {
            return "Quantités";
        }
        return "Quantities";
    }

    public static function Quantities_saved($lang) {
        if ($lang == "fr") {
            return "Les quantités ont été sauvegardées";
        }
        return "Quantities have been saved";
    }

    public static function SupplementariesInfo($lang) {
        if ($lang == "fr") {
            return "Informations complémentaires";
        }
        return "Supplementaries info";
    }

    public static function Color_codes($lang) {
        if ($lang == "fr") {
            return "Codes couleur";
        }
        return "Color codes";
    }

    public static function color_code($lang) {
        if ($lang == "fr") {
            return "Code couleur";
        }
        return "Color code";
    }

    public static function Block_Resouces($lang) {
        if ($lang == "fr") {
            return "Bloquer ressources";
        }
        return "Block resouces";
    }

    public static function Edit_color_code($lang) {
        if ($lang == "fr") {
            return "Modifier code couleur";
        }
        return "Edit color code";
    }

    public static function Color($lang) {
        if ($lang == "fr") {
            return "Couleur";
        }
        return "Color";
    }

    public static function Text($lang) {
        if ($lang == "fr") {
            return "Texte";
        }
        return "Text";
    }

    public static function Display_order($lang) {
        if ($lang == "fr") {
            return "Ordre d'affichage";
        }
        return "Display order";
    }

    public static function Edit_scheduling($lang) {
        if ($lang == "fr") {
            return "Modifier horaires";
        }
        return "Edit scheduling";
    }

    public static function Availables_days($lang) {
        if ($lang == "fr") {
            return "Jours disponibles";
        }
        return "Availables days";
    }

    public static function DaysList($lang) {
        if ($lang == "fr") {
            return array("Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi", "Dimanche");
        }
        return array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday");
    }

    public static function Day_beginning($lang) {
        if ($lang == "fr") {
            return "Début journée";
        }
        return "Day beginning";
    }

    public static function Day_end($lang) {
        if ($lang == "fr") {
            return "Fin journée";
        }
        return "Day end";
    }

    public static function Booking_size_bloc($lang) {
        if ($lang == "fr") {
            return "Résolution du bloc de réservation";
        }
        return "Booking size bloc";
    }

    public static function The_user_specify($lang) {
        if ($lang == "fr") {
            return "L'utilisateur spécifie";
        }
        return "The user specifies";
    }

    public static function the_booking_duration($lang) {
        if ($lang == "fr") {
            return "La durée de la réservation";
        }
        return "the booking duration";
    }

    public static function the_date_time_when_reservation_ends($lang) {
        if ($lang == "fr") {
            return "La date et l'heure de fin de la réservation";
        }
        return "the date/time when reservation ends";
    }

    public static function booking_time_scale($lang) {
        if ($lang == "fr") {
            return "Echelle de temps par défaut";
        }
        return "Default booking scale";
    }

    public static function Minutes($lang = "") {
        if ($lang == "fr") {
            return "Minutes";
        }
        return "Minutes";
    }

    public static function Hours($lang = "") {
        if ($lang == "fr") {
            return "Heures";
        }
        return "Hours";
    }

    public static function Days($lang = "") {
        if ($lang == "fr") {
            return "Jours";
        }
        return "Days";
    }

    static public function Default_color($lang) {
        if ($lang == "fr") {
            return "Couleur par défaut";
        }
        return "Default color";
    }

    static public function Display($lang) {
        if ($lang == "fr") {
            return "Affichage";
        }
        return "Display";
    }

    static public function Header_Color($lang) {
        if ($lang == "fr") {
            return "Couleur d'entête";
        }
        return "Header color";
    }

    static public function Header_Text($lang) {
        if ($lang == "fr") {
            return "Texte d'entête";
        }
        return "Header text";
    }

    static public function Header_font_size($lang) {
        if ($lang == "fr") {
            return "Taille texte d'entête";
        }
        return "Header font size";
    }

    static public function Resa_font_size($lang) {
        if ($lang == "fr") {
            return "Taille texte réservation";
        }
        return "Reservation font size";
    }

    static public function Header_height($lang) {
        if ($lang == "fr") {
            return "Hauteur d'entête";
        }
        return "Header height";
    }

    static public function Line_height($lang) {
        if ($lang == "fr") {
            return "Hauteur de ligne";
        }
        return "Line height";
    }

    public static function DateFromTime($time, $lang) {
        $dayStream = date("l", $time);
        $monthStream = date("F", $time);
        $dayNumStream = date("d", $time);
        $yearStream = date("Y", $time);
        $sufixStream = date("S", $time);

        if ($lang == "fr") {

            return BookingTranslator::translateDayFromEn($dayStream, $lang) . " " . $dayNumStream . " " . BookingTranslator::translateMonthFromEn($monthStream, $lang) . " " . $yearStream;

            // setlocale(LC_TIME, "fr_FR");
            // return utf8_encode(strftime('%A %d %B %Y', $time));
        }
        // english

        return $dayStream . ", " . $monthStream . " " . $dayNumStream . $sufixStream . " " . $yearStream;
    }

    public static function This_week($lang) {
        if ($lang == "fr") {
            return "Cette semaine";
        }
        return "This week";
    }

    public static function Phone($lang) {
        if ($lang == "fr") {
            return "Tél.";
        }
        return "Phone";
    }

    public static function Short_desc($lang) {
        if ($lang == "fr") {
            return "courte desc.";
        }
        return "Short desc.";
    }

    public static function Desc($lang) {
        if ($lang == "fr") {
            return "Desc.";
        }
        return "Desc.";
    }

    public static function Fromdate($lang) {
        if ($lang == "fr") {
            return "Période du";
        }
        return "From";
    }

    public static function ToDate($lang) {
        if ($lang == "fr") {
            return "au";
        }
        return "to";
    }

    public static function Today($lang) {
        if ($lang == "fr") {
            return "Aujourd'hui";
        }
        return "Today";
    }

    public static function Day($lang) {
        if ($lang == "fr") {
            return "Jour";
        }
        return "Day";
    }

    public static function Week_Area($lang = "") {
        if ($lang == "fr") {
            return "Semainier";
        }
        return "Week Area";
    }

    public static function Day_Area($lang = "") {
        if ($lang == "fr") {
            return "Jour Domaine";
        }
        return "Day Area";
    }

    public static function Week($lang = "") {
        if ($lang == "fr") {
            return "Semaine";
        }
        return "Week";
    }

    public static function Month($lang = "") {
        if ($lang == "fr") {
            return "Mois";
        }
        return "Month";
    }

    public static function Accessibilities($lang = "") {
        if ($lang == "fr") {
            return "Accès";
        }
        return "Accessibilities";
    }

    public static function User($lang = "") {
        if ($lang == "fr") {
            return "Utilisateur";
        }
        return "User";
    }

    public static function Authorized_users($lang = "") {
        if ($lang == "fr") {
            return "Listing des autorisations";
        }
        return "Authorized users list";
    }

    public static function Manager($lang) {
        if ($lang == "fr") {
            return "Gestionnaire";
        }
        return "Manager";
    }

    public static function Admin($lang) {
        if ($lang == "fr") {
            return "Administrateur";
        }
        return "Admin";
    }

    public static function This_month($lang) {
        if ($lang == "fr") {
            return "Ce mois";
        }
        return "This month";
    }

    public static function Edit_Reservation($lang = "") {
        if ($lang == "fr") {
            return "Modification Réservation";
        }
        return "Edit Reservation";
    }

    public static function Resource($lang = "") {
        if ($lang == "fr") {
            return "Ressource";
        }
        return "Resource";
    }

    public static function booking_on_behalf_of($lang = "") {
        if ($lang == "fr") {
            return "Réserver au nom de";
        }
        return "booking on behalf of";
    }

    public static function Short_description($lang = "") {
        if ($lang == "fr") {
            return "Description courte";
        }
        return "Short description";
    }

    public static function Full_description($lang = "") {
        if ($lang == "fr") {
            return "Description complète";
        }
        return "Full description";
    }

    public static function Beginning_of_the_reservation($lang = "") {
        if ($lang == "fr") {
            return "Début de la réservation";
        }
        return "Beginning of the reservation";
    }

    public static function End_of_the_reservation($lang = "") {
        if ($lang == "fr") {
            return "Fin de la réservation";
        }
        return "End of the reservation";
    }

    public static function Duration($lang = "") {
        if ($lang == "fr") {
            return "Durée";
        }
        return "Duration";
    }

    public static function time($lang = "") {
        if ($lang == "fr") {
            return "horaire";
        }
        return "time";
    }

    public static function translateDayFromEn($day, $lang) {
        if ($day == "Monday") {
            return BookingTranslator::Monday($lang);
        }
        if ($day == "Tuesday") {
            return BookingTranslator::Tuesday($lang);
        }
        if ($day == "Wednesday") {
            return BookingTranslator::Wednesday($lang);
        }
        if ($day == "Thursday") {
            return BookingTranslator::Thursday($lang);
        }
        if ($day == "Friday") {
            return BookingTranslator::Friday($lang);
        }
        if ($day == "Saturday") {
            return BookingTranslator::Saturday($lang);
        }
        if ($day == "Sunday") {
            return BookingTranslator::Sunday($lang);
        }
    }

    public static function Monday($lang = "") {
        if ($lang == "fr") {
            return "Lundi";
        }
        return "Monday";
    }

    public static function Tuesday($lang = "") {
        if ($lang == "fr") {
            return "Mardi";
        }
        return "Tuesday";
    }

    public static function Wednesday($lang = "") {
        if ($lang == "fr") {
            return "Mercredi";
        }
        return "Wednesday";
    }

    public static function Thursday($lang = "") {
        if ($lang == "fr") {
            return "Jeudi";
        }
        return "Thursday";
    }

    public static function Friday($lang = "") {
        if ($lang == "fr") {
            return "Vendredi";
        }
        return "Friday";
    }

    public static function Saturday($lang = "") {
        if ($lang == "fr") {
            return "Samedi";
        }
        return "Saturday";
    }

    public static function Sunday($lang = "") {
        if ($lang == "fr") {
            return "Dimanche";
        }
        return "Sunday";
    }

    public static function Reservation_number($lang) {
        if ($lang == "fr") {
            return "Nombre de réservations";
        }
        return "Reservation number";
    }

    public static function translateMonthFromEn($day, $lang) {
        if ($lang == "fr") {
            if ($day == "January") {
                return "Janvier";
            }
            if ($day == "February") {
                return "Février";
            }
            if ($day == "March") {
                return "Mars";
            }
            if ($day == "April") {
                return "Avril";
            }
            if ($day == "May") {
                return "Mai";
            }
            if ($day == "June") {
                return "Juin";
            }
            if ($day == "July") {
                return "Juillet";
            }
            if ($day == "August") {
                return "Août";
            }
            if ($day == "September") {
                return "Septembre";
            }
            if ($day == "October") {
                return "Octobre";
            }
            if ($day == "November") {
                return "Novembre";
            }
            if ($day == "December") {
                return "Décembre";
            }
        }
    }

    public static function Packages_saved($lang) {
        if ($lang == "fr") {
            return "Les forfaits ont été sauvegardés";
        }
        return "Packages have been saved";
    }

    public static function Is_mandatory($lang) {
        if ($lang == "fr") {
            return "Champ obligatoire";
        }
        return "Is mandatory";
    }

    public static function Supplementaries_saved($lang) {
        if ($lang == "fr") {
            return "Les suppléments ont été sauvegardés";
        }
        return "Supplementaries have been saved";
    }

    static public function Use_Package($lang) {
        if ($lang == "fr") {
            return "Utiliser forfait";
        }
        return "Use package";
    }

    static public function Select_Package($lang) {
        if ($lang == "fr") {
            return "Choix forfait";
        }
        return "Select package";
    }

    static public function Booking_summary_options($lang) {
        if ($lang == "fr") {
            return "Options d'affichage des réservations";
        }
        return "Booking summary options";
    }

    static public function Edit_booking_options($lang) {
        if ($lang == "fr") {
            return "Options de réservation";
        }
        return "Edit booking options";
    }

    static public function Description_fields($lang) {
        if ($lang == "fr") {
            return "Champs de description";
        }
        return "Description fields";
    }

    static public function Both_short_and_full_description($lang) {
        if ($lang == "fr") {
            return "Description courte et longue";
        }
        return "Both short and full description";
    }

    static public function Only_short_description($lang) {
        if ($lang == "fr") {
            return "Description courte uniquement";
        }
        return "Only short description";
    }

    static public function Only_full_description($lang) {
        if ($lang == "fr") {
            return "Description longue uniquement";
        }
        return "Only full description";
    }

    static public function EditReservationPlugin($lang) {
        if ($lang == "fr") {
            return "Page éditer réservation";
        }
        return "Edit reservation plugin";
    }

    static public function Url($lang) {
        if ($lang == "fr") {
            return "Url";
        }
        return "Url";
    }

    static public function EditBookingMailing($lang) {
        if ($lang == "fr") {
            return "Emails lors de la réservation";
        }
        return "Edit booking mailing";
    }

    static public function Send_emails($lang) {
        if ($lang == "fr") {
            return "Envoie de couriels";
        }
        return "Send emails";
    }

    static public function Never($lang) {
        if ($lang == "fr") {
            return "Jamais";
        }
        return "Never";
    }

    public static function WhenAUserBook($lang) {
        if ($lang == "Fr") {
            return "Lorsqu'un utilisateur réserve";
        }
        return "When a user book";
    }

    public static function When_manager_admin_edit_a_reservation($lang) {
        if ($lang == "Fr") {
            return "Lorsqu'un gestionnaire modifie un réservation";
        }
        return "When manager/admin edit a reservation";
    }

    public static function EmailManagers($lang) {
        if ($lang == "Fr") {
            return "Prévenir les gestionnaires";
        }
        return "Email managers";
    }

}

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
            return "Le module Booking permet de réserver des resources dans un calendrier";
        }
        return "The Booking module allows to book resources in an agenda";
    }

    public static function bookingstatisticauthorizations($lang) {
        if ($lang == "fr") {
            return "Stats autorisations";
        }
        return "Authorizations stats";
    }

    public static function bookingauthorizedusers($lang) {
        if ($lang == "fr") {
            return "Listing autorisations";
        }
        return "Authorizations lists";
    }

    public static function bookingprices($lang) {
        if ($lang == "fr") {
            return "Tarifs";
        }
        return "Prices";
    }

    public static function bookinginvoice($lang) {
        if ($lang == "fr") {
            return "Nouveau relevé";
        }
        return "New invoice";
    }

    public static function bookingreservationstats($lang) {
        if ($lang == "fr") {
            return "Stats réservations";
        }
        return "Reservations stats";
    }

    public static function bookinggrrstats($lang) {
        if ($lang == "fr") {
            return "Stats personalisés";
        }
        return "Manual stats";
    }

    public static function configuration($lang = "") {
        if ($lang == "fr") {
            return "Configuration de \"Booking\"";
        }
        return "Booking configuration";
    }

    public static function bookingsettings($lang) {
        if ($lang == "fr") {
            return "Calendrier config";
        }
        return "booking settings";
    }

    public static function bookingusersstats($lang) {
        if ($lang == "fr") {
            return "Utilisateurs ayant réservé";
        }
        return "booking users";
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

    public static function Package($lang) {
        if ($lang == "fr") {
            return "Forfait";
        }
        return "Package";
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
            return "Informations sup";
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
        if ($lang == "fr") {
            return "Lorsqu'un utilisateur réserve";
        }
        return "When a user book";
    }

    public static function When_manager_admin_edit_a_reservation($lang) {
        if ($lang == "fr") {
            return "Lorsqu'un gestionnaire modifie un réservation";
        }
        return "When manager/admin edit a reservation";
    }

    public static function EmailManagers($lang) {
        if ($lang == "fr") {
            return "Prévenir les gestionnaires";
        }
        return "Email managers";
    }

    public static function reservationError($lang) {
        if ($lang == "fr") {
            return "Erreur: il y a déjà une réservation sur ce créneau";
        }
        return "Error: There is already a reservation for the given slot";
    }

    public static function reservationSuccess($lang) {
        if ($lang == "fr") {
            return "Succès: la réservation a bien été enregistrée";
        }
        return "Success: Your reservation has been saved";
    }

    static public function Authorisations_for($lang) {
        if ($lang == "fr") {
            return "Autorisations pour ";
        }
        return "Authorisations for";
    }

    public static function Active_Authorizations($lang = "") {
        if ($lang == "fr") {
            return "Autorisations actives";
        }
        return "Active Authorizations";
    }

    public static function Unactive_Authorizations($lang = "") {
        if ($lang == "fr") {
            return "Autorisations non actives";
        }
        return "Inactive Authorizations";
    }

    static public function Modifications_have_been_saved($lang) {
        if ($lang == "fr") {
            return "Les modifications ont bien été enregistrées";
        }
        return "Modifications have been saved";
    }

    static public function Nightwe($lang) {
        if ($lang == "fr") {
            return "Nuit et WE";
        }
        return "Night & WE";
    }

    public static function Unique_price($lang = "") {
        if ($lang == "fr") {
            return "Tarif unique";
        }
        return "Unique pricing";
    }

    public static function Price_night($lang = "") {
        if ($lang == "fr") {
            return "Tarif de nuit";
        }
        return "Night rate";
    }

    public static function Edit_NightWE($lang = "") {
        if ($lang == "fr") {
            return "Editer Nuit et WE";
        }
        return "Edit Night & WE";
    }

    public static function Night_beginning($lang = "") {
        if ($lang == "fr") {
            return "Début nuit";
        }
        return "Night beginning";
    }

    public static function Night_end($lang = "") {
        if ($lang == "fr") {
            return "Fin nuit";
        }
        return "Night end";
    }

    public static function Price_weekend($lang = "") {
        if ($lang == "fr") {
            return "Tarif de week-end";
        }
        return "Week-end rate";
    }

    public static function Weekend_days($lang = "") {
        if ($lang == "fr") {
            return "Jours week-end";
        }
        return "Week-end days";
    }

    public static function PricesBooking($lang = "") {
        if ($lang == "fr") {
            return "Tarifs réservation";
        }
        return "Prices booking";
    }

    public static function Prices($lang = "") {
        if ($lang == "fr") {
            return "Tarifs";
        }
        return "Prices";
    }

    public static function night($lang = "") {
        if ($lang == "fr") {
            return "nuit";
        }
        return "night";
    }

    public static function WE($lang = "") {
        if ($lang == "fr") {
            return "WE";
        }
        return "WE";
    }

    public static function Invoice_booking($lang = "") {
        if ($lang == "fr") {
            return "Facturer les réservations";
        }
        return "Invoice booking";
    }

    public static function Invoice_All($lang = "") {
        if ($lang == "fr") {
            return "Facturer tout";
        }
        return "Invoice all";
    }

    public static function Invoice_Responsible($lang = "") {
        if ($lang == "fr") {
            return "Facturer un responsable";
        }
        return "Invoice a person in charge";
    }

    public static function Details($lang = "") {
        if ($lang == "fr") {
            return "Details";
        }
        return "Details";
    }

    public static function Number($lang = "") {
        if ($lang == "fr") {
            return "Numéro";
        }
        return "Number";
    }

    public static function Recipient($lang = "") {
        if ($lang == "fr") {
            return "Bénéficiaire";
        }
        return "Recipient";
    }

    public static function Date_Begin($lang = "") {
        if ($lang == "fr") {
            return "Date de début";
        }
        return "Date begin";
    }

    public static function Date_End($lang = "") {
        if ($lang == "fr") {
            return "Date de fin";
        }
        return "Date end";
    }

    public static function Authorisations_statistics($lang = "") {
        if ($lang == "fr") {
            return "Statistiques autorisations";
        }
        return "Authorisations statistics";
    }

    public static function PeriodBegining($lang = "") {
        if ($lang == "fr") {
            return "Début période";
        }
        return "Period begining";
    }

    public static function PeriodEnd($lang = "") {
        if ($lang == "fr") {
            return "Fin période";
        }
        return "Period end";
    }

    public static function Jan($lang) {
        if ($lang == "fr") {
            return "Janv.";
        }
        return "Jan.";
    }

    public static function Feb($lang) {
        if ($lang == "fr") {
            return "Févr.";
        }
        return "Feb.";
    }

    public static function Mar($lang) {
        if ($lang == "fr") {
            return "Mars";
        }
        return "Mar.";
    }

    public static function Apr($lang) {
        if ($lang == "fr") {
            return "Avri.";
        }
        return "Apr.";
    }

    public static function May($lang) {
        if ($lang == "fr") {
            return "Mai";
        }
        return "May";
    }

    public static function Jun($lang) {
        if ($lang == "fr") {
            return "Juin";
        }
        return "June";
    }

    public static function July($lang) {
        if ($lang == "fr") {
            return "Juil.";
        }
        return "July";
    }

    public static function Aug($lang) {
        if ($lang == "fr") {
            return "Août";
        }
        return "Aug.";
    }

    public static function Sept($lang) {
        if ($lang == "fr") {
            return "Sept.";
        }
        return "Sept.";
    }

    public static function Oct($lang) {
        if ($lang == "fr") {
            return "Oct.";
        }
        return "Oct.";
    }

    public static function Nov($lang) {
        if ($lang == "fr") {
            return "Nov.";
        }
        return "Nov.";
    }

    public static function Dec($lang) {
        if ($lang == "fr") {
            return "Déc.";
        }
        return "Dec.";
    }

    public static function Annual_review_of_the_number_of_reservations_of($lang) {
        if ($lang == "fr") {
            return "Bilan annuel du nombre de réservations pour ";
        }
        return "Annual review of the number of reservations for ";
    }

    public static function Annual_review_of_the_time_of_reservations_of($lang) {
        if ($lang == "fr") {
            return "Bilan annuel du temps de réservation pour ";
        }
        return "Annual review of the time of reservations for ";
    }

    public static function Booking_time_year($lang) {
        if ($lang == "fr") {
            return "Nombre d'heures de réservation par ressource dans l'année";
        }
        return "Time (in hours) of reservations for each resource during the given period";
    }

    public static function Booking_time_year_category($lang) {
        if ($lang == "fr") {
            return "Nombre d'heures de réservation par catégories de ressources dans la période";
        }
        return "Time (in hours) of reservations for each resource category during the given period";
    }

    public static function Booking_number_year($lang) {
        if ($lang == "fr") {
            return "Nombre de réservations par ressource dans l'année";
        }
        return "Number of reservations during the year";
    }

    public static function Booking_number_year_category($lang) {
        if ($lang == "fr") {
            return "Nombre de réservations par catégorie de ressources dans la période";
        }
        return "Number of reservations for each resource category during the given period";
    }

    public static function query($lang) {
        if ($lang == "fr") {
            return "Requête";
        }
        return "Query";
    }

    public static function Area($lang = "") {
        if ($lang == "fr") {
            return "Domaines";
        }
        return "Areas";
    }

    public static function Contains($lang) {
        if ($lang == "fr") {
            return "Contient";
        }
        return "Contains";
    }

    public static function Does_not_contain($lang) {
        if ($lang == "fr") {
            return "Ne contient pas";
        }
        return "Does not contain";
    }

    public static function Output($lang) {
        if ($lang == "fr") {
            return "Export";
        }
        return "Output";
    }

    public static function Calendar_Default_view($lang = "") {
        if ($lang == "fr") {
            return "Vue par défaut dans le calendrier";
        }
        return "Calendar default view";
    }

    public static function Default_view($lang = "") {
        if ($lang == "fr") {
            return "Vue par défaut";
        }
        return "Default view";
    }

    public static function Calendar_View($lang = "") {
        if ($lang == "fr") {
            return "Vue calendrier";
        }
        return "Calendar View";
    }

    public static function Additional_info($lang = "") {
        if ($lang == "fr") {
            return "Informations réservation";
        }
        return "Additional info";
    }

    public static function block_resources($lang) {
        if ($lang == "fr") {
            return "Bloquer des ressources";
        }
        return "Block resources";
    }
    
    public static function RemoveReservation($lang){
        if ($lang == "fr") {
            return "Etes vous sure de vouloir supprimer la réservation ?";
        }
        return "Are you sure you want to delete this reservation ?";
    }
    
    public static function SendEmailsToUsers($lang){
        if ($lang == "fr") {
            return "Prévenir les autres utilisateurs de la machine que le créneau est libéré:";
        }
        return "Inform other users of that the slot is free:";
    }
    
   
            
            

}

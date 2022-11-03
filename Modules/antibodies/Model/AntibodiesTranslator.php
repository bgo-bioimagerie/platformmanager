<?php

/**
 * Class to translate the syggrif views
 * 
 * @author sprigent
 *
 */
class AntibodiesTranslator {

    public static function antibodies($lang) {
        if ($lang == "fr") {
            return "Anticorps";
        }
        return "Antibodies";
    }

    public static function antibodiesConfigAbstract($lang) {
        if ($lang == "fr") {
            return "Le module Antibodies permet de gérer une base de donnée d'anticorps";
        }
        return "The Antibodies module allows to manage an antibodies data base";
    }

    public static function configuration($lang = "") {
        if ($lang == "fr") {
            return "Configuration de \"Antibodies\"";
        }
        return "Antibodies configuration";
    }

    public static function Install_Repair_database($lang = "") {
        if ($lang == "fr") {
            return "Installer/Réparer la base de données";
        }
        return "Install/Repair database";
    }

    public static function Install_Txt($lang = "") {
        if ($lang == "fr") {
            return "Cliquer sur \"Installer\" pour installer ou réparer la base de données de 'Antibodies'.
                    Cela créera les tables qui n'existent pas";
        }
        return "To repair the Antibodies mudule, click \"Install\". This will create the
                Antibodies tables in the database if they don't exists ";
    }

    public static function Activate_desactivate_menus($lang = "") {
        if ($lang == "fr") {
            return "Activer/désactiver les menus";
        }
        return "Activate/deactivate menus";
    }

    public static function Export_as_csv($lang) {
        if ($lang == "fr") {
            return "Exporter en csv";
        }
        return "Export as csv";
    }

    public static function AntibodyInfo($lang) {
        if ($lang == "fr") {
            return "Informations anticorps";
        }
        return "Antibody informations";
    }

    public static function Number($lang) {
        if ($lang == "fr") {
            return "Numéro";
        }
        return "Number";
    }

    public static function Provider($lang) {
        if ($lang == "fr") {
            return "Fournisseur";
        }
        return "Provider";
    }

    public static function Source($lang) {
        if ($lang == "fr") {
            return "Source";
        }
        return "Source";
    }

    public static function Reference($lang) {
        if ($lang == "fr") {
            return "Reference";
        }
        return "Reference";
    }

    public static function Reactivity($lang) {
        if ($lang == "fr") {
            return "Réactivité";
        }
        return "Reactivity";
    }

    public static function AcClone($lang) {
        if ($lang == "fr") {
            return "Clone";
        }
        return "Clone";
    }

    public static function Lot($lang) {
        if ($lang == "fr") {
            return "Lot";
        }
        return "Lot";
    }

    public static function Stockage($lang) {
        if ($lang == "fr") {
            return "Stockage";
        }
        return "Stockage";
    }
    
    public static function Application($lang) {
        if ($lang == "fr") {
            return "Application";
        }
        return "Application";
    }
    
    public static function Export_catalog($lang) {
        if ($lang == "fr") {
            return "Exporter au catalogue";
        }
        return "Export catalog";
    }
    
    public static function Ref_protocol($lang) {
        if ($lang == "fr") {
            return "Ref protocol";
        }
        return "Ref protocol";
    }
    
    public static function Dilution($lang) {
        if ($lang == "fr") {
            return "Dilution";
        }
        return "Dilution";
    }
    
    public static function Comment($lang) {
        if ($lang == "fr") {
            return "Commentaire";
        }
        return "Comment";
    }
 
    public static function Espece($lang) {
        if ($lang == "fr") {
            return "Espèce";
        }
        return "Espece";
    }
    
    public static function Organe($lang) {
        if ($lang == "fr") {
            return "Organe";
        }
        return "Organe";
    }    
    
    public static function Valide($lang) {
        if ($lang == "fr") {
            return "Valide";
        }
        return "Valide";
    }   
    
    public static function Ref_bloc($lang) {
        if ($lang == "fr") {
            return "Ref bloc";
        }
        return "Ref bloc";
    }    
    
    public static function Prelevement($lang) {
        if ($lang == "fr") {
            return "Prelevement";
        }
        return "Prelevement";
    }
    
    public static function Image($lang) {
        if ($lang == "fr") {
            return "Image";
        }
        return "Image";
    }
    
    public static function Disponible($lang) {
        if ($lang == "fr") {
            return "Disponible";
        }
        return "Disponible";
    }
    
    public static function Date_recept($lang) {
        if ($lang == "fr") {
            return "Date reception";
        }
        return "Date reception";
    }
    
    public static function No_dossier($lang) {
        if ($lang == "fr") {
            return "No dossier";
        }
        return "No dossier";
    }
    
    public static function Tissus($lang) {
        if ($lang == "fr") {
            return "Tissus";
        }
        return "Tissus";
    }

    public static function Owner($lang) {
        if ($lang == "fr") {
            return "Propriétaires";
        }
        return "Owner";
    }    
 
    public static function Staining($lang) {
        if ($lang == "fr") {
            return "Marquage";
        }
        return "Staining";
    } 
    
    public static function Isotype($lang) {
        if ($lang == "fr") {
            return "Isotype";
        }
        return "Isotype";
    } 
    
    public static function AntibodyInfoHaveBeenSaved($lang) {
        if ($lang == "fr") {
            return "L'anticorps à été sauvegardé";
        }
        return "Antibody info have been saved";
    } 
 
    public static function addTissus($lang) {
        if ($lang == "fr") {
            return "Ajouter tissus";
        }
        return "Add Tissus";
    } 

    public static function addOwner($lang) {
        if ($lang == "fr") {
            return "Ajouter propriétaire";
        }
        return "Add owner";
    }     
    
    public static function All($lang) {
        if ($lang == "fr") {
            return "Tous";
        }
        return "All";
    }  
    
    public static function ConfirmDeleteAntibody($lang) {
        if ($lang == "fr") {
            return "Etes-vous sûr de vouloir suprimer cet anticorps ?";
        }
        return "Are you sure to delete the antibody?";
    }

    public static function MissingItems($lang) {
        if ($lang == "fr") {
            return "Certains éléments doivent être créés en amont de l'enregistrement de votre anticorps : ";
        }
        return "Some items must be created in order to being able to record this antibody: ";
    }
    
}

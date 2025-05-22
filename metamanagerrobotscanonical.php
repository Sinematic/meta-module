<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class MetaManagerRobotsCanonical extends Module
{
    public function __construct()
    {
        $this->name = 'metamanagerrobotscanonical';
        $this->tab = 'seo';
        $this->version = '1.0.0';
        $this->author = 'Maxime Rache - Drive Only';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Meta Manager (Robots & Canonical');
        $this->description = $this->l("
            Gère la définition des balises robots et canonical selon le contexte pour stratégie SEO :
            - ajoute automatiquement des balises < noindex, follow > sur les pages paginées (à partir de la page 2)
            - définit pour balise canonical l'URL nettoyée des paramètres (pas de tri selon des critères tels que le prix, renvoie vers la page 1 si c'est une pagination)
        ");

        $this->ps_versions_compliancy = ['min' => '1.7', 'max' => _PS_VERSION_];
    }

    public function install() {

        return parent::install() && $this->registerHook('header');
    }

    public function hookHeader($params) {

        $context = Context::getContext();
        $domain = Tools::getShopDomain(true); // http(s) + driveonly.com
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        $canonicalUrl = $domain . $requestUri; // Valeur par défaut
        $metaTag = '<meta name="robots" content="index,follow">'; // Comportement par défaut : index, follow

        // Recherche de paramètres page ou order dans l'URL
        if (strpos($requestUri, 'page=') !== false || strpos($requestUri, 'order=') !== false) {

            $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $canonicalUrl = $domain . $path; // URL reconstituée sans les paramètres

            // URL contenant order= aura toujours noindex, follow et pour canonical la page sans paramètres
            if (strpos($requestUri, 'order=') !== false) {

                $metaTag = '<meta name="robots" content="noindex,follow">';

            } else { // Si contient page mais pas order
                // Récupération de la valeur de page 
                preg_match('/page=([0-9]+)/', $requestUri, $matches);
                $pageNumber = isset($matches[1]) ? (int)$matches[1] : 1;

                if ($pageNumber >= 2) {
                    $metaTag = '<meta name="robots" content="noindex,follow">';
                }
            }
        }

        // Assure que la variable Smarty est toujours définie
        $context->smarty->assign('canonical_page', $canonicalUrl);
        $context->smarty->assign('meta_tag_robots', $metaTag);
    }

}
// Dernier problème: domaine renvoie en http et non https
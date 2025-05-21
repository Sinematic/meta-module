<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class PaginationMeta extends Module
{
    public function __construct()
    {
        $this->name = 'paginationmeta';
        $this->tab = 'seo';
        $this->version = '1.0.0';
        $this->author = 'Maxime Rache - Drive Only';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Pagination Meta Manager');
        $this->description = $this->l('
            Ajoute automatiquement des balises < noindex, follow > sur les pages paginées (à partir de 2). 
            Modifie la canonical afin de renvoyer vers la page 1 de la pagination.
        ');

        $this->ps_versions_compliancy = ['min' => '1.7', 'max' => _PS_VERSION_];
    }

    public function install()
    {
        return parent::install() && $this->registerHook('header');
    }

    public function hookHeader($params) {
        $context = Context::getContext();
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';

        $canonicalPageOne = $requestUri; // Valeur par défaut

        // Vérification de la présence de "page=" dans l'URL
        if (strpos($requestUri, 'page=') !== false) {
            // Récupération de la valeur de page
            preg_match('/page=([0-9]+)/', $requestUri, $matches);
            $pageNumber = isset($matches[1]) ? (int)$matches[1] : 1;

            if ($pageNumber >= 2) {
                // Ajout de <noindex, follow> à partir de la seconde page
                $metaTag = '<meta name="robots" content="noindex,follow">';
                $context->smarty->assign('pagination_meta_manager', $metaTag);

                // Remplacement de la valeur de page par page=1
                $canonicalPageOne = preg_replace('/page=[0-9]+/', 'page=1', $requestUri);
            } elseif ($pageNumber === 1) {
                // Nettoyage de l'URL si page=1
                $canonicalPageOne = preg_replace('/[\?&]?page=1(&|$)/', '$1', $requestUri);
                $canonicalPageOne = rtrim($canonicalPageOne, '?&');
            }
        }

        // Assure que la variable Smarty est toujours définie
        $context->smarty->assign('pagination_page_one', $canonicalPageOne);
    }
}

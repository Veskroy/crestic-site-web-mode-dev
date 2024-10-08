<?php

namespace App\Controller\Admin;

use App\Entity\Actualites;
use App\Entity\Agenda;
use App\Entity\CategorieDocument;
use App\Entity\CategorieProjet;
use App\Entity\Cms;
use App\Entity\Configuration;
use App\Entity\Data;
use App\Entity\Departements;
use App\Entity\Documents;
use App\Entity\DocumentsInternes;
use App\Entity\Emplois;
use App\Entity\Equipes;
use App\Entity\EquipesHasMembres;
use App\Entity\EquipesHasSliders;
use App\Entity\Financeurs;
use App\Entity\MailingList;
use App\Entity\MembresCrestic;
use App\Entity\Organigramme;
use App\Entity\Partenaires;
use App\Entity\Plateformes;
use App\Entity\Projets;
use App\Entity\ProjetsHasEquipes;
use App\Entity\ProjetsHasMembres;
use App\Entity\ProjetsHasSliders;
use App\Entity\Sites;
use App\Entity\Slider;
use App\Repository\MembresCresticRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class DashboardController extends AbstractDashboardController
{
    public function __construct(
        protected MembresCresticRepository $membresCresticRepository,
        protected ChartBuilderInterface $chartBuilder)
    {
    }
    #[Route(path: '/administration', name: 'admin')]
    public function index(

    ): Response
    {
        $membres = $this->membresCresticRepository->findMembreCrestic([]);
        $keys = Data::TAB_STATUS_FORM;
        unset($keys['']);
        $keys = array_values($keys);

        $data = [];
        $total = 0;
        foreach ($keys as $status) {
            $data[$status] = 0;
        }

        foreach ($membres as $membre) {
            $data[$membre->getStatus()]++;
            $total ++;
        }

        $chart = $this->chartBuilder->createChart(Chart::TYPE_PIE);

        $chart->setData([
            'labels' => $keys,
            'datasets' => [
                [
                    'label' => 'Membres du CReSTIC',
                    'data' => array_values($data),
                ],
            ],
        ]);

        return $this->render('admin/dashboard.html.twig',
            [
                'chart' => $chart,
                'data' => $data,
                'totalMembres' => $total
            ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('CReSTIC');
    }

    public function configureAssets(): Assets
    {
        return parent::configureAssets()
            ->addWebpackEncoreEntry('app');
    }

    public function configureMenuItems(): iterable
    {
        //todo:lien vers profil, vers equipes et vers projets

        yield MenuItem::linktoDashboard('Dashboard', 'fa fa-home');

        yield MenuItem::section('Actualités');
        yield MenuItem::linkToCrud('Actualités', 'fa fa-newspaper', Actualites::class)->setPermission('ROLE_ADMINISTRATEUR');
        yield MenuItem::linkToCrud('Agenda', 'fa fa-calendar-days', Agenda::class)->setPermission('ROLE_ADMINISTRATEUR');
        yield MenuItem::linkToCrud('Emplois', 'fa fa-briefcase', Emplois::class);
        yield MenuItem::linkToCrud('Documents', 'fa fa-book', Documents::class);

        yield MenuItem::section('Intranet');

        yield MenuItem::linkToRoute('Documents Internes', 'fa fa-file-text', 'admin_documents');

        yield MenuItem::section('Publications')->setPermission('ROLE_ADMINISTRATEUR');
        yield MenuItem::linkToRoute('Statistiques de publication','fa fa-chart-simple', 'admin_statistiques_publications')->setPermission('ROLE_ADMINISTRATEUR');

        yield MenuItem::section('Projets');
        yield MenuItem::linkToCrud('Projets', 'fa fa-layer-group', Projets::class);
        yield MenuItem::linkToCrud('Catégories de projet', 'fa fa-list', CategorieProjet::class)->setPermission('ROLE_ADMINISTRATEUR');
        yield MenuItem::linkToCrud('Projets / Sliders', 'fa fa-file-powerpoint', ProjetsHasSliders::class);
        yield MenuItem::linkToCrud('Projets / Membres', 'fa fa-user', ProjetsHasMembres::class);
        yield MenuItem::linkToCrud('Projets / Equipes', 'fa fa-user-group', ProjetsHasEquipes::class);
        yield MenuItem::linkToCrud('Financeurs', 'fa fa-hand-holding-dollar', Financeurs::class);
        yield MenuItem::linkToCrud('Partenaires', 'fa fa-handshake-simple', Partenaires::class);

        yield MenuItem::section('Equipes');
        yield MenuItem::linkToCrud('Equipes', 'fa fa-user-group', Equipes::class);
        yield MenuItem::linkToCrud('Equipes / Membres', 'fa fa-user', EquipesHasMembres::class);
        yield MenuItem::linkToCrud('Equipes / Sliders', 'fa fa-file-powerpoint', EquipesHasSliders::class);

        yield MenuItem::section('Structure du laboratoire');
        yield MenuItem::linkToCrud('Membres', 'fa fa-user', MembresCrestic::class)->setPermission('ROLE_ADMINISTRATEUR');
        yield MenuItem::linkToCrud('Listes de diffusion', 'fa fa-envelope',MailingList::class);
        yield MenuItem::linkToCrud('Départements', 'fa fa-building', Departements::class)->setPermission('ROLE_ADMINISTRATEUR');
        yield MenuItem::linkToCrud('Plateformes', 'fa fa-server', Plateformes::class)->setPermission('ROLE_ADMINISTRATEUR');

        yield MenuItem::section('Sites web')->setPermission('ROLE_ADMINISTRATEUR');
        yield MenuItem::linkToCrud('Sites', 'fa fa-globe', Sites::class)->setPermission('ROLE_ADMINISTRATEUR');
        yield MenuItem::linkToCrud('Pages', 'fa fa-file-lines', Cms::class)->setPermission('ROLE_ADMINISTRATEUR');
        yield MenuItem::linkToCrud('Slides', 'fa fa-file-powerpoint', Slider::class);
        yield MenuItem::linkToCrud('Organigrammes', 'fa fa-chart-line', Organigramme::class)->setPermission('ROLE_ADMINISTRATEUR');
        yield MenuItem::linkToCrud('Configurations', 'fa fa-gear', Configuration::class)->setPermission('ROLE_ADMINISTRATEUR');
    }
}

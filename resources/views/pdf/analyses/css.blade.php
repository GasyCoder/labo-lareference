<style>
    /* Reset et styles de base */

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    @page {
        margin: 18px;
        size: A4;
    }

    .bold {
        font-weight: bold;
    }

    body {
        font-family: Arial, sans-serif;
        font-size: 11pt;
        color: black;
        line-height: 1.1;
        margin: 0;  /* Ajouté */
        padding: 0; /* Ajouté */
    }

    /* En-tête */
    .header-section {
        width: 100%;
        display: block;
        margin: 0;
        padding: 0;
        line-height: 0;
    }

    .header-logo {
        width: 100%;
        object-fit: contain;
        object-position: left top;
        margin: 0;
        padding: 0;
        display: block;
        max-height: 70px;
    }

    /* Section contenu */
    .content-wrapper {
        padding: 0 30px;
        max-height: 100vh;
        overflow: hidden;
    }

    /* QR code */
    .doctor-info {
        float: right;
        margin-top: 13px;
        position: absolute;
        right: 40px;
        top: 60px;
    }

    /* Information patient */
    .patient-info {
        line-height: 1.5;
        width: 70%;
        margin: 5px 0;     /* Réduit de 9px à 5px */
        font-size: 10pt;
    }

    /* Tables principales */
    .main-table {
        width: 100%;
        border-collapse: collapse;
        margin: 0;
        padding: 0;
        page-break-inside: auto;  /* Ajouté */
    }

    .main-table tr {
        page-break-inside: avoid;    /* Ajouté */
        page-break-after: auto;      /* Ajouté */
    }

    .signature img {
        max-width: 150px; /* Réduit de 180px */
    }

    .main-table td {
        vertical-align: middle;
        padding: 0px 0;    /* Réduit de 1px à 0px */
        line-height: 1.1;  /* Réduit de 1.2 */
    }

    /* En-tête de section */
    .section-header {
        margin-top: 8px;
    }

    /* Ligne rouge */
    .red-line {
        border-top: 0.5px solid #FF0000;
        margin: 1px 0;
        width: 100%;
    }

        /* Ajuster la taille du texte principal */
        .col-designation,
        .col-resultat,
        .col-valref,
        .col-anteriorite {
            font-size: 10.5pt;  /* Légèrement réduit de 10.5pt */
    }

  /* Colonnes */
    .col-designation {
        width: 40%;
        text-align: left;
        padding-right: 10px;
    }

    .col-resultat {
        width: 20%;
        text-align: left;
        padding-left: 20px;
    }

    .col-valref {
        width: 20%;
        text-align: left;
        padding-left: 20px;
    }

    .col-anteriorite {
        width: 8%;
        padding-left: 10px;
        text-align: left;
    }

    /* Styles des titres */
    .section-title {
        color: #FF0000;
        font-weight: bold;
        text-transform: uppercase;
        font-size: 10pt;   /* Réduit la taille du titre */
        padding: 2px 0;    /* Réduit l'espacement */
    }

    .header-cols {
        font-size: 8pt;
        color: #000;
    }

    /* Niveaux de hiérarchie */
    .parent-row {
        font-weight: bold;
    }

    .child-row td:first-child {
        padding-left: 20px;
    }

    .subchild-row td:first-child {
        padding-left: 40px;
    }

    /* Formatage des valeurs */
    .value-cell {
        white-space: nowrap;
    }

    .unit {
        padding-left: 1px;
    }

    /* Styles spéciaux */
    .bold {
        font-weight: bold;
    }

    .indent-1 {
        padding-left: 20px !important;
    }

    .indent-2 {
        padding-left: 40px !important;
    }

    /* Watermark */
    .watermark {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) rotate(-45deg);
        font-size: 72px;
        color: rgba(169, 169, 169, 0.2);
        z-index: -1;
        width: 100%;
        text-align: center;
    }

    /* Aperçu */
    .preview-banner {
        background-color: #f8d7da;
        color: #721c24;
        padding: 10px;
        margin: 10px 0;
        border: 1px solid #f5c6cb;
        border-radius: 4px;
        text-align: center;
        font-weight: bold;
    }

    /* Résultats non validés */
    .not-validated-result {
        color: #6c757d;
        font-style: italic;
    }

    /* Pied de page */
    .footer {
        margin-top: 10px;
        font-size: 10pt;
        text-align: center;
        color: #6c757d;
    }

    /* Signature */
    .signature {
        margin-top: 20px;
        text-align: right;
        padding-right: 40px;
    }

    /* Alignements spécifiques */
    .text-right {
        text-align: right;
    }

    .text-left {
        text-align: left;
    }

    /* Espacement */
    .spacing {
        height: 3px;
    }

    /* Impression */
    @media print {
        body {
            padding: 0;
            margin: 0;
        }

        .content-wrapper {
            padding: 0 20px;
        }

        .main-table {
            page-break-inside: avoid;
        }

        .section-break {
            page-break-before: always;
        }

        .watermark {
            display: none;
        }

         /* Ajouté pour la gestion des sauts de page */
        .section-title {
            page-break-after: avoid;
        }

        .red-line + .main-table {
            page-break-before: avoid;
        }

        .signature {
            page-break-inside: avoid;
        }
    }
    .antibiogramme-header {
        font-weight: bold;
        margin-top: 5px;
    }

    .antibiogramme-group {
        margin-top: 5px;
    }

    .antibiogramme-item {
        padding-left: 40px !important;
    }

    /* Ajoutez ces styles dans votre CSS */
    .germe-isole {
        padding-left: 20px !important;
    }

    .antibiogramme-section {
        padding-left: 40px !important;
    }

    .antibiotic-row {
        padding-left: 40px !important;
        display: flex;
        justify-content: space-between;
    }

    .antibiotic-name {
        flex: 1;
        text-align: left;
    }

    .antibiotic-result {
        text-align: left;
        padding-right: 20px;
    }


</style>

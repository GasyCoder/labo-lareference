
<style>
    /* Styles pour l'en-tête */
    .icon-circle {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: rgba(var(--bs-primary-rgb), 0.1);
    }

    .icon-circle i {
        font-size: 1.25rem;
    }

    /* Carte d'information */
    .info-card {
        display: flex;
        align-items: center;
        padding: 0.75rem;
        background-color: #f8f9fa;
        border-radius: 0.5rem;
        transition: all 0.3s ease;
    }

    .info-card:hover {
        background-color: #fff;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    .info-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    .info-content {
        flex: 1;
    }

    .info-content label {
        display: block;
        font-size: 0.75rem;
        color: #6c757d;
        margin-bottom: 0.25rem;
    }

    .info-content strong {
        display: block;
        color: #212529;
        font-size: 1rem;
    }

    /* Badges et boutons */
    .badge {
        padding: 0.5em 1em;
        font-weight: 500;
    }

    .btn {
        padding: 0.5rem 1.25rem;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .btn:hover {
        transform: translateY(-1px);
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .col-lg-4 {
            margin-top: 1rem;
        }

        .info-card {
            margin-bottom: 0.5rem;
        }
    }
    </style>

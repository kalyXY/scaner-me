<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\AttendanceService;
use App\Models\Payment;
use App\Models\ExamAuthorization;
use App\Services\LoggingService;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class DashboardController extends BaseController
{
    private AttendanceService $attendanceService;
    private Payment $paymentModel;
    private ExamAuthorization $examAuthModel;
    private Environment $twig;

    public function __construct(
        AttendanceService $attendanceService = null,
        Payment $paymentModel = null,
        ExamAuthorization $examAuthModel = null,
        LoggingService $logger = null
    ) {
        parent::__construct($logger);
        $this->attendanceService = $attendanceService ?? new AttendanceService();
        $this->paymentModel = $paymentModel ?? new Payment();
        $this->examAuthModel = $examAuthModel ?? new ExamAuthorization();
        
        // Initialize Twig
        $loader = new FilesystemLoader(__DIR__ . '/../../templates');
        $this->twig = new Environment($loader, [
            'cache' => false, // Disable cache for development
            'debug' => true,
        ]);
    }

    public function index(): void
    {
        try {
            $date = $_GET['date'] ?? date('Y-m-d');

            // Validate date format
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                $date = date('Y-m-d');
            }

            // Get data for dashboard
            $sessions = $this->attendanceService->getSessionsForDate($date);
            $payments = $this->paymentModel->getRecentPayments(20);
            $authorizations = $this->examAuthModel->getRecentAuthorizations(20);

            // Render template
            echo $this->twig->render('dashboard.html.twig', [
                'date' => $date,
                'sessions' => $sessions,
                'payments' => $payments,
                'authorizations' => $authorizations,
                'title' => 'Tableau de bord - Système de présence QR'
            ]);

        } catch (\Throwable $e) {
            $this->logger->error('Dashboard error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Fallback to simple error page
            http_response_code(500);
            echo '<h1>Erreur du système</h1><p>Une erreur est survenue. Veuillez réessayer plus tard.</p>';
        }
    }

    public function apiData(): void
    {
        try {
            $date = $_GET['date'] ?? date('Y-m-d');

            // Validate date format
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                $this->errorResponse('Invalid date format. Use YYYY-MM-DD', 400);
                return;
            }

            $sessions = $this->attendanceService->getSessionsForDate($date);
            $payments = $this->paymentModel->getRecentPayments(20);
            $authorizations = $this->examAuthModel->getRecentAuthorizations(20);

            $this->successResponse([
                'date' => $date,
                'sessions' => $sessions,
                'payments' => $payments,
                'authorizations' => $authorizations
            ]);

        } catch (\Throwable $e) {
            $this->logger->error('Dashboard API error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->errorResponse('Internal server error', 500);
        }
    }
}
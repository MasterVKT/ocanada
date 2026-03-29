<?php

declare(strict_types=1);

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class RoleBoundaryFeatureTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    private bool $dbReady = false;

    /** @var list<int> */
    private array $createdUserIds = [];

    /** @var list<int> */
    private array $createdEmployeIds = [];

    protected function setUp(): void
    {
        parent::setUp();

        try {
            $db = db_connect();
            $this->dbReady = $db->tableExists('utilisateurs') && $db->tableExists('employes');
        } catch (Throwable) {
            $this->dbReady = false;
        }
    }

    protected function tearDown(): void
    {
        if ($this->dbReady) {
            $db = db_connect();

            if ($this->createdEmployeIds !== []) {
                $db->table('employes')->whereIn('id', $this->createdEmployeIds)->delete();
            }

            if ($this->createdUserIds !== []) {
                $db->table('utilisateurs')->whereIn('id', $this->createdUserIds)->delete();
            }
        }

        parent::tearDown();
    }

    public function testEmployeeCannotAccessAdminDashboard(): void
    {
        $session = $this->buildSessionForRole('employe');
        if ($session === null) {
            $this->markTestSkipped('Database not ready for auth-boundary tests.');
        }

        $result = $this->withSession($session)->get('/admin/dashboard');
        $result->assertStatus(403);
    }

    public function testEmployeeCannotAccessSharedRealtime(): void
    {
        $session = $this->buildSessionForRole('employe');
        if ($session === null) {
            $this->markTestSkipped('Database not ready for auth-boundary tests.');
        }

        $result = $this->withSession($session)->get('/shared/realtime');
        $result->assertStatus(403);
    }

    public function testEmployeeCannotAccessRealtimeApi(): void
    {
        $session = $this->buildSessionForRole('employe');
        if ($session === null) {
            $this->markTestSkipped('Database not ready for auth-boundary tests.');
        }

        $result = $this->withSession($session)->get('/api/presences/today');
        $result->assertStatus(403);
    }

    public function testAdminCanAccessFinanceModule(): void
    {
        $session = $this->buildSessionForRole('admin');
        if ($session === null) {
            $this->markTestSkipped('Database not ready for auth-boundary tests.');
        }

        $result = $this->withSession($session)->get('/admin/finance');
        $result->assertStatus(200);
    }

    public function testAdminCanAccessAuditAndConfigurationModules(): void
    {
        $session = $this->buildSessionForRole('admin');
        if ($session === null) {
            $this->markTestSkipped('Database not ready for auth-boundary tests.');
        }

        $this->withSession($session)->get('/admin/audit')->assertStatus(200);
        $this->withSession($session)->get('/admin/configuration')->assertStatus(200);
        $this->withSession($session)->get('/admin/rapports')->assertStatus(200);
    }

    public function testAdminCanExportFinanceCsvAndEmployeeCannot(): void
    {
        $adminSession = $this->buildSessionForRole('admin');
        $employeeSession = $this->buildSessionForRole('employe');

        if ($adminSession === null || $employeeSession === null) {
            $this->markTestSkipped('Database not ready for auth-boundary tests.');
        }

        $this->withSession($adminSession)
            ->get('/admin/finance/export-csv')
            ->assertStatus(200);

        $this->withSession($employeeSession)
            ->get('/admin/finance/export-csv')
            ->assertStatus(403);
    }

    public function testLegacyAdminAndEmployeeAliasesRedirectToNormalizedRoutes(): void
    {
        $adminSession = $this->buildSessionForRole('admin');
        $employeeSession = $this->buildSessionForRole('employe');

        if ($adminSession === null || $employeeSession === null) {
            $this->markTestSkipped('Database not ready for auth-boundary tests.');
        }

        $this->withSession($adminSession)
            ->get('/admin/conges')
            ->assertStatus(302)
            ->assertRedirectTo('/admin/leaves');

        $this->withSession($employeeSession)
            ->get('/employe/conges')
            ->assertStatus(302)
            ->assertRedirectTo('/employe/leaves');

        $this->withSession($employeeSession)
            ->get('/employe/planning-hebdo')
            ->assertStatus(302)
            ->assertRedirectTo('/employe/planning');
    }

    public function testAgentCannotAccessAdminAuditModule(): void
    {
        $session = $this->buildSessionForRole('agent');
        if ($session === null) {
            $this->markTestSkipped('Database not ready for auth-boundary tests.');
        }

        $this->withSession($session)->get('/admin/audit')->assertStatus(403);
    }

    public function testAdminCanAccessReportsFormAndEmployeeCannot(): void
    {
        $adminSession = $this->buildSessionForRole('admin');
        $employeeSession = $this->buildSessionForRole('employe');

        if ($adminSession === null || $employeeSession === null) {
            $this->markTestSkipped('Database not ready for auth-boundary tests.');
        }

        $this->withSession($adminSession)->get('/admin/rapports')->assertStatus(200);
        $this->withSession($employeeSession)->get('/admin/rapports')->assertStatus(403);

        $this->withSession($adminSession)
            ->post('/admin/rapports/generer', [
                'type' => 'presences_mensuel',
                'format' => 'csv',
                'start' => date('Y-m-01'),
                'end' => date('Y-m-d'),
            ])
            ->assertStatus(200);

        $this->withSession($employeeSession)
            ->post('/admin/rapports/generer', [
                'type' => 'presences_mensuel',
                'format' => 'csv',
                'start' => date('Y-m-01'),
                'end' => date('Y-m-d'),
            ])
            ->assertStatus(403);

        $this->withSession($adminSession)
            ->post('/admin/rapports/generer', [
                'type' => 'conges_annuels',
                'format' => 'csv',
                'start' => date('Y-01-01'),
                'end' => date('Y-12-31'),
            ])
            ->assertStatus(200);

        $this->withSession($employeeSession)
            ->post('/admin/rapports/generer', [
                'type' => 'conges_annuels',
                'format' => 'csv',
                'start' => date('Y-01-01'),
                'end' => date('Y-12-31'),
            ])
            ->assertStatus(403);

        $this->withSession($adminSession)
            ->post('/admin/rapports/generer', [
                'type' => 'visiteurs',
                'format' => 'csv',
                'start' => date('Y-m-01'),
                'end' => date('Y-m-d'),
            ])
            ->assertStatus(200);

        $this->withSession($employeeSession)
            ->post('/admin/rapports/generer', [
                'type' => 'visiteurs',
                'format' => 'csv',
                'start' => date('Y-m-01'),
                'end' => date('Y-m-d'),
            ])
            ->assertStatus(403);
    }

    /**
     * @return array<string,mixed>|null
     */
    private function buildSessionForRole(string $role): ?array
    {
        if (! $this->dbReady) {
            return null;
        }

        $db = db_connect();

        $email = sprintf('%s-test-%s@example.com', $role, bin2hex(random_bytes(4)));

        $db->table('utilisateurs')->insert([
            'email' => $email,
            'mot_de_passe' => password_hash('TempPass123!', PASSWORD_DEFAULT),
            'role' => $role,
            'statut' => 'actif',
            'date_creation' => date('Y-m-d H:i:s'),
        ]);

        $userId = (int) $db->insertID();
        $this->createdUserIds[] = $userId;

        $employeId = null;
        if (in_array($role, ['employe', 'agent'], true)) {
            $matricule = sprintf('TST%s', str_pad((string) $userId, 6, '0', STR_PAD_LEFT));
            $db->table('employes')->insert([
                'utilisateur_id' => $userId,
                'matricule' => $matricule,
                'nom' => 'Role',
                'prenom' => ucfirst($role),
                'email' => $email,
                'date_embauche' => date('Y-m-d'),
                'statut' => 'actif',
                'date_creation' => date('Y-m-d H:i:s'),
            ]);
            $employeId = (int) $db->insertID();
            $this->createdEmployeIds[] = $employeId;

            $db->table('utilisateurs')->where('id', $userId)->update(['employe_id' => $employeId]);
        }

        return [
            'logged_in' => true,
            'user_id' => $userId,
            'role' => $role,
            'employe_id' => $employeId,
        ];
    }
}

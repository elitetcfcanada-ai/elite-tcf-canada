<?php
/**
 * Section tarifs + modale de paiement. Inclure après config + $pdo.
 * Les pages peuvent définir avant inclusion :
 *   $tcf_subscription_section_id = 'Abonnement'; // id de l’ancre (optionnel)
 */
require_once __DIR__ . '/subscription_plans_data.php';
require_once __DIR__ . '/subscription_access.php';
require_once __DIR__ . '/platform_settings.php';
require_once __DIR__ . '/payment_config.php';

if (!isset($pdo) || !tcf_subscription_sales_enabled($pdo)) {
    return;
}

$__tcf_sub_section_id = isset($tcf_subscription_section_id) ? (string) $tcf_subscription_section_id : 'Abonnement';

$__tcf_sub_viewer_type = 'free';
$__tcf_sub_viewer = null;
if (!empty($_SESSION['user_id']) && isset($pdo)) {
    try {
        $st = $pdo->prepare('SELECT id, role, subscription_type, subscription_expires_at, created_at FROM users WHERE id = ?');
        $st->execute([(int) $_SESSION['user_id']]);
        $__tcf_sub_viewer = $st->fetch(PDO::FETCH_ASSOC) ?: null;
        if ($__tcf_sub_viewer && isset($__tcf_sub_viewer['subscription_type'])) {
            $__tcf_sub_viewer_type = (string) $__tcf_sub_viewer['subscription_type'];
        }
    } catch (Throwable $e) {
    }
}
$__tcf_sub_active = tcf_user_has_premium_access($__tcf_sub_viewer);

// ── Masquer toute la section abonnement tant que l'abonnement est actif ──
// Elle réapparaîtra automatiquement dès l'expiration.
if ($__tcf_sub_active) {
    return;
}

$__tcf_sub_active_until = '';

?>
<section class="subscription-section" id="<?php echo htmlspecialchars($__tcf_sub_section_id); ?>" aria-labelledby="tcf-subscription-heading">
    <div class="subscription-header">
        <h4 class="section-subtitle tcf-sub-kicker" id="tcf-subscription-kicker"><i class='bx bxs-school'></i> ABONNEMENT</h4>
        <h2 class="section-title tfc-sub-main-title" id="tcf-subscription-heading">Les Meilleurs <span class="tcf-sub-accent">Promotions</span> Juste Pour <span class="tcf-sub-accent">Vous</span></h2>
        <div class="tcf-sub-title-bar" aria-hidden="true"></div>
    </div>

    <div class="pricing-grid" style="display: flex; flex-wrap: wrap; gap: 1rem; justify-content: center;">
        <?php foreach (tcf_subscription_plans_catalog() as $plan): ?>
            <?php
            $isCurrent = ($__tcf_sub_active && $__tcf_sub_viewer_type === $plan['key']);
            $cardClass = 'pricing-card' . ($isCurrent ? ' pricing-card--current' : '');
            $planFeatures = isset($plan['features']) && is_array($plan['features']) && $plan['features'] !== []
                ? $plan['features']
                : tcf_subscription_default_features();
            $priceNum = isset($plan['price']) ? (float) $plan['price'] : 0.0;
            $priceDisplay = fmod($priceNum, 1.0) < 0.001 ? (string) (int) round($priceNum) : number_format($priceNum, 2, '.', '');
            ?>
            <article class="<?php echo htmlspecialchars($cardClass); ?>" data-plan-key="<?php echo htmlspecialchars($plan['key']); ?>" style="flex: 0 0 calc(50% - 0.5rem); min-width: 280px; max-width: 400px;" data-responsive-card="true">
                <div class="card-header">
                    <h3 class="plan-name"><?php echo htmlspecialchars($plan['tier']); ?></h3>
                    <div class="period-badge"><?php echo htmlspecialchars($plan['badge']); ?></div>
                    <div class="price">
                        <span class="currency"><?php echo htmlspecialchars((string) ($plan['currency'] ?? '$')); ?></span><?php echo htmlspecialchars($priceDisplay); ?>
                    </div>
                    <div class="wave-shape" aria-hidden="true">
                        <svg viewBox="0 0 400 40" preserveAspectRatio="none">
                            <path d="M0,20 Q100,0 200,20 T400,20 L400,40 L0,40 Z" fill="#141622" />
                        </svg>
                    </div>
                </div>
                <div class="card-content">
                    <?php if ($isCurrent): ?>
                        <p class="tcf-sub-current-pill" role="status">Votre formule actuelle</p>
                    <?php endif; ?>
                    <ul class="features-list">
                        <?php foreach ($planFeatures as $feat): ?>
                            <li class="feature-item">
                                <i class="bx bx-check" aria-hidden="true"></i>
                                <span><?php echo htmlspecialchars((string) $feat); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="subscribe-btn js-tcf-open-checkout<?php echo $__tcf_sub_active ? ' is-disabled' : ''; ?>"
                        data-plan-key="<?php echo htmlspecialchars($plan['key']); ?>"
                        data-plan-label="<?php echo htmlspecialchars($plan['tier'] . ' — ' . $plan['badge']); ?>"
                        data-plan-price="<?php echo htmlspecialchars((string) $priceNum); ?>"
                        data-plan-currency="<?php echo htmlspecialchars((string) ($plan['currency'] ?? '$')); ?>"
                        data-plan-xaf="<?php echo (int) ($plan['payment_xaf'] ?? tcf_subscription_payment_xaf_amount()); ?>"<?php echo $__tcf_sub_active ? ' disabled aria-disabled="true"' : ''; ?>>
                        <?php if ($__tcf_sub_active): ?>
                            ABONNÉ ACTIF<?php echo $__tcf_sub_active_until !== '' ? ' jusqu’au ' . htmlspecialchars($__tcf_sub_active_until) : ''; ?>
                        <?php else: ?>
                            S'ABONNER
                        <?php endif; ?>
                    </button>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<!-- Modale de paiement d'abonnement -->
<div id="payment-modal" class="payment-modal" hidden>
    <div class="payment-modal-overlay" id="payment-modal-overlay"></div>
    <div class="payment-modal-content">
        <button type="button" class="payment-modal-close" id="payment-modal-close" aria-label="Fermer">
            <i class='bx bx-x'></i>
        </button>
        
        <div class="payment-modal-header">
            <h3 class="payment-modal-title">Paiement de l'abonnement</h3>
        </div>
        
        <div class="payment-modal-body">
            <div class="payment-plan-info" id="payment-plan-info">
                <span class="payment-plan-label">Formule :</span>
                <span class="payment-plan-value" id="payment-plan-name">-</span>
                <span class="payment-plan-price" id="payment-plan-price">-</span>
            </div>
            
            <form id="payment-form" class="payment-form">
                <div class="form-group">
                    <label for="payment-phone" class="form-label">Numéro de téléphone</label>
                    <input 
                        type="tel" 
                        id="payment-phone" 
                        name="phone" 
                        class="form-input" 
                        placeholder="+237 6XX XXX XXX"
                        required
                        autocomplete="tel"
                        inputmode="tel"
                    >
                </div>
                
                <div class="payment-modal-actions">
                    <button type="button" class="btn btn-secondary" id="payment-cancel">Annuler</button>
                    <button type="submit" class="btn btn-primary" id="payment-submit">
                        <span class="btn-text">Payer maintenant</span>
                        <span class="btn-loader" hidden>
                            <i class='bx bx-loader-alt bx-spin'></i>
                        </span>
                    </button>
                </div>
            </form>
            
            <div class="payment-status" id="payment-status" hidden>
                <div class="payment-status-message" id="payment-status-message"></div>
            </div>
        </div>
    </div>
</div>

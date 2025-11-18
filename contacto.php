<?php
require_once 'config/config.php';

$vehiculo_id = $_GET['vehiculo_id'] ?? null;
$vehiculo = null;

if ($vehiculo_id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM vehiculos WHERE id = ?");
    $stmt->execute([$vehiculo_id]);
    $vehiculo = $stmt->fetch();
}
?>
<?php
$page_title = 'Contacto';
include 'includes/head.php';
?>
<style>
    body {
        background: linear-gradient(135deg, #f8fafc 0%, #e0e7ff 100%);
        min-height: 100vh;
    }
    
    .contact-hero {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
        padding: 3rem 0;
        margin-bottom: 3rem;
    }
    
    .contact-card {
        border: none;
        border-radius: 1rem;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }
    
    .vehicle-info-card {
        background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
        border-left: 4px solid #3b82f6;
        border-radius: 0.5rem;
        padding: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .form-control, .form-select {
        border-radius: 0.5rem;
        border: 2px solid #e2e8f0;
        padding: 0.75rem 1rem;
        transition: all 0.3s ease;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    
    .btn-submit {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        border: none;
        padding: 0.875rem 2rem;
        font-weight: 600;
        border-radius: 0.5rem;
        transition: all 0.3s ease;
    }
    
    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(59, 130, 246, 0.3);
    }
    
    .alert-success-custom {
        display: none;
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        border: none;
        border-radius: 0.5rem;
        color: #065f46;
        padding: 1rem 1.25rem;
    }
</style>

<?php include 'includes/navbar.php'; ?>

<div class="contact-hero">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <h1 class="display-4 fw-bold mb-3">Contáctanos</h1>
                <p class="lead mb-0">Estamos aquí para ayudarte a encontrar el vehículo perfecto</p>
            </div>
        </div>
    </div>
</div>

<div class="container mb-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <?php if ($vehiculo): ?>
                <div class="vehicle-info-card">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-car-front fs-1 text-primary me-3"></i>
                        <div>
                            <h4 class="fw-bold mb-1">Consulta sobre: <?= htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo']) ?></h4>
                            <p class="text-muted mb-0">Año: <?= htmlspecialchars($vehiculo['año']) ?> | Precio: <?= formatPrice($vehiculo['precio']) ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="card contact-card">
                <div class="card-body p-5">
                    <h2 class="h3 fw-bold text-dark mb-4">
                        <?= $vehiculo ? 'Consultar sobre este vehículo' : 'Envíanos un mensaje' ?>
                    </h2>
                    
                    <div class="alert alert-success-custom alert-dismissible fade show" role="alert" id="successAlert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <strong>¡Consulta enviada!</strong> Nos pondremos en contacto contigo pronto.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    
                    <form id="contactForm">
                        <?php if ($vehiculo_id): ?>
                            <input type="hidden" name="vehiculo_id" value="<?= $vehiculo_id ?>">
                        <?php endif; ?>
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control" 
                                       name="nombre" 
                                       placeholder="Tu nombre completo" 
                                       required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                                <input type="email" 
                                       class="form-control" 
                                       name="email" 
                                       placeholder="tu@email.com" 
                                       required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Teléfono <span class="text-danger">*</span></label>
                            <input type="tel" 
                                   class="form-control" 
                                   name="telefono" 
                                   placeholder="+1234567890" 
                                   required>
                        </div>
                        
                        <?php if (!$vehiculo_id): ?>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Asunto</label>
                                <select class="form-select" name="asunto">
                                    <option value="">Selecciona un asunto</option>
                                    <option value="consulta_general">Consulta General</option>
                                    <option value="informacion_vehiculo">Información sobre un Vehículo</option>
                                    <option value="financiamiento">Financiamiento</option>
                                    <option value="otro">Otro</option>
                                </select>
                            </div>
                        <?php endif; ?>
                        
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Mensaje <span class="text-danger">*</span></label>
                            <textarea class="form-control" 
                                      name="mensaje" 
                                      rows="6" 
                                      placeholder="<?= $vehiculo ? 'Escribe tu consulta sobre este vehículo...' : 'Escribe tu mensaje aquí...' ?>" 
                                      required></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-submit w-100">
                            <i class="bi bi-send me-2"></i> Enviar Consulta
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <p class="text-muted mb-2">¿Prefieres llamarnos?</p>
                <a href="tel:+1234567890" class="btn btn-outline-primary">
                    <i class="bi bi-telephone me-2"></i> Llamar ahora
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('contactForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enviando...';
        
        fetch('api/consultas.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('successAlert').style.display = 'block';
                this.reset();
                
                if (typeof notifications !== 'undefined') {
                    notifications.success('Consulta enviada exitosamente');
                }
                
                // Scroll to alert
                document.getElementById('successAlert').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                
                // Si hay vehículo, redirigir después de 2 segundos
                <?php if ($vehiculo_id): ?>
                    setTimeout(() => {
                        window.location.href = 'detalle.php?id=<?= $vehiculo_id ?>';
                    }, 2000);
                <?php endif; ?>
            } else {
                if (typeof notifications !== 'undefined') {
                    notifications.error(data.message || 'Error al enviar consulta');
                } else {
                    alert(data.message || 'Error al enviar consulta');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (typeof notifications !== 'undefined') {
                notifications.error('Error al enviar consulta. Por favor, intenta nuevamente.');
            } else {
                alert('Error al enviar consulta');
            }
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    });
</script>

<?php include 'includes/footer.php'; ?>


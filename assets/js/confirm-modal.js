/**
 * Sistema de Modales de Confirmación Moderno
 * Reemplaza los confirm() nativos con modales elegantes
 */
class ConfirmModal {
    constructor() {
        this.modal = null;
        this.init();
    }

    init() {
        // Crear modal si no existe
        if (!document.getElementById('confirm-modal')) {
            this.modal = document.createElement('div');
            this.modal.id = 'confirm-modal';
            this.modal.className = 'confirm-modal-overlay';
            this.modal.innerHTML = `
                <div class="confirm-modal">
                    <div class="confirm-modal-icon">
                        <i class="bi bi-question-circle-fill"></i>
                    </div>
                    <h3 class="confirm-modal-title">Confirmar Acción</h3>
                    <p class="confirm-modal-message"></p>
                    <div class="confirm-modal-actions">
                        <button class="btn-confirm-cancel">Cancelar</button>
                        <button class="btn-confirm-ok">Confirmar</button>
                    </div>
                </div>
            `;
            document.body.appendChild(this.modal);
            this.attachStyles();
        } else {
            this.modal = document.getElementById('confirm-modal');
        }
    }

    show(message, title = 'Confirmar Acción', type = 'warning') {
        return new Promise((resolve) => {
            const modalContent = this.modal.querySelector('.confirm-modal');
            const modalIcon = this.modal.querySelector('.confirm-modal-icon i');
            const modalTitle = this.modal.querySelector('.confirm-modal-title');
            const modalMessage = this.modal.querySelector('.confirm-modal-message');
            const btnCancel = this.modal.querySelector('.btn-confirm-cancel');
            const btnOk = this.modal.querySelector('.btn-confirm-ok');

            // Configurar contenido
            modalTitle.textContent = title;
            modalMessage.textContent = message;

            // Configurar icono según tipo
            const icons = {
                warning: 'bi-exclamation-triangle-fill',
                danger: 'bi-x-circle-fill',
                info: 'bi-info-circle-fill',
                question: 'bi-question-circle-fill'
            };
            modalIcon.className = `bi ${icons[type] || icons.question}`;

            // Configurar colores según tipo
            modalContent.className = `confirm-modal confirm-modal-${type}`;

            // Limpiar listeners anteriores
            const newBtnCancel = btnCancel.cloneNode(true);
            const newBtnOk = btnOk.cloneNode(true);
            btnCancel.parentNode.replaceChild(newBtnCancel, btnCancel);
            btnOk.parentNode.replaceChild(newBtnOk, btnOk);

            // Event listeners
            newBtnCancel.addEventListener('click', () => {
                this.hide();
                resolve(false);
            });

            newBtnOk.addEventListener('click', () => {
                this.hide();
                resolve(true);
            });

            // Cerrar al hacer clic fuera del modal
            this.modal.addEventListener('click', (e) => {
                if (e.target === this.modal) {
                    this.hide();
                    resolve(false);
                }
            });

            // Cerrar con ESC
            const escHandler = (e) => {
                if (e.key === 'Escape') {
                    this.hide();
                    resolve(false);
                    document.removeEventListener('keydown', escHandler);
                }
            };
            document.addEventListener('keydown', escHandler);

            // Mostrar modal
            this.modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        });
    }

    hide() {
        this.modal.classList.remove('show');
        document.body.style.overflow = '';
    }

    attachStyles() {
        const style = document.createElement('style');
        style.textContent = `
            .confirm-modal-overlay {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                backdrop-filter: blur(4px);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 10000;
                opacity: 0;
                visibility: hidden;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                padding: 1rem;
            }

            .confirm-modal-overlay.show {
                opacity: 1;
                visibility: visible;
            }

            .confirm-modal {
                background: white;
                border-radius: 1rem;
                padding: 2rem;
                max-width: 420px;
                width: 100%;
                box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
                transform: scale(0.9) translateY(20px);
                transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
                text-align: center;
            }

            .confirm-modal-overlay.show .confirm-modal {
                transform: scale(1) translateY(0);
            }

            .confirm-modal-icon {
                margin-bottom: 1rem;
            }

            .confirm-modal-icon i {
                font-size: 4rem;
                display: block;
            }

            .confirm-modal-warning .confirm-modal-icon i {
                color: #f59e0b;
            }

            .confirm-modal-danger .confirm-modal-icon i {
                color: #ef4444;
            }

            .confirm-modal-info .confirm-modal-icon i {
                color: #3b82f6;
            }

            .confirm-modal-question .confirm-modal-icon i {
                color: #667eea;
            }

            .confirm-modal-title {
                font-size: 1.5rem;
                font-weight: 700;
                color: #1e293b;
                margin-bottom: 0.75rem;
            }

            .confirm-modal-message {
                font-size: 1rem;
                color: #64748b;
                line-height: 1.6;
                margin-bottom: 2rem;
            }

            .confirm-modal-actions {
                display: flex;
                gap: 0.75rem;
                justify-content: center;
            }

            .btn-confirm-cancel,
            .btn-confirm-ok {
                padding: 0.75rem 1.5rem;
                border-radius: 0.5rem;
                font-weight: 600;
                font-size: 0.9375rem;
                border: none;
                cursor: pointer;
                transition: all 0.2s ease;
                min-width: 120px;
            }

            .btn-confirm-cancel {
                background: #f1f5f9;
                color: #64748b;
            }

            .btn-confirm-cancel:hover {
                background: #e2e8f0;
                color: #475569;
                transform: translateY(-2px);
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            }

            .btn-confirm-ok {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
            }

            .btn-confirm-ok:hover {
                background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
                transform: translateY(-2px);
                box-shadow: 0 4px 6px -1px rgba(102, 126, 234, 0.3);
            }

            .confirm-modal-danger .btn-confirm-ok {
                background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            }

            .confirm-modal-danger .btn-confirm-ok:hover {
                background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
                box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.3);
            }

            @media (max-width: 576px) {
                .confirm-modal {
                    padding: 1.5rem;
                }

                .confirm-modal-icon i {
                    font-size: 3rem;
                }

                .confirm-modal-title {
                    font-size: 1.25rem;
                }

                .confirm-modal-actions {
                    flex-direction: column;
                }

                .btn-confirm-cancel,
                .btn-confirm-ok {
                    width: 100%;
                }
            }
        `;
        document.head.appendChild(style);
    }
}

// Instancia global
const confirmModal = new ConfirmModal();

// Función helper para reemplazar confirm() nativo
async function confirmAction(message, title = 'Confirmar Acción', type = 'warning') {
    return await confirmModal.show(message, title, type);
}


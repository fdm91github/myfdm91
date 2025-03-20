<!-- Modale per l'aggiunta di un portafoglio -->
<div class="modal fade" id="addWalletModal" tabindex="-1" aria-labelledby="addWalletModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg"> <!-- enlarged modal for icon grid -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addWalletModalLabel">Aggiungi un portafoglio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="addWalletStatus"></div>
                <form id="addWalletForm">
                    <div class="form-group mb-3">
                        <label for="addWalletDescription">Descrizione</label>
                        <input type="text" name="description" id="addWalletDescription" class="form-control" placeholder="Descrizione" required>
                    </div>

                    <!-- Icon selection using clickable icons -->
                    <div class="form-group mb-3">
                        <label>Icona</label>
                        <div class="d-flex flex-wrap" id="iconSelection">
                            <div class="m-2 icon-item" data-icon="cart4"><i class="bi bi-cart4" style="font-size: 2rem;"></i></div>
                            <div class="m-2 icon-item" data-icon="bag-check-fill"><i class="bi bi-bag-check-fill" style="font-size: 2rem;"></i></div>
                            <div class="m-2 icon-item" data-icon="bag-heart-fill"><i class="bi bi-bag-heart-fill" style="font-size: 2rem;"></i></div>
                            <div class="m-2 icon-item" data-icon="bag-fill"><i class="bi bi-bag-fill" style="font-size: 2rem;"></i></div>
                            <div class="m-2 icon-item" data-icon="bandaid-fill"><i class="bi bi-bandaid-fill" style="font-size: 2rem;"></i></div>
                            <div class="m-2 icon-item" data-icon="graph-up"><i class="bi bi-graph-up" style="font-size: 2rem;"></i></div>
                            <div class="m-2 icon-item" data-icon="basket3-fill"><i class="bi bi-basket3-fill" style="font-size: 2rem;"></i></div>
                            <div class="m-2 icon-item" data-icon="safe-fill"><i class="bi bi-safe-fill" style="font-size: 2rem;"></i></div>
                            <div class="m-2 icon-item" data-icon="bank2"><i class="bi bi-bank2" style="font-size: 2rem;"></i></div>
                            <div class="m-2 icon-item" data-icon="cash-coin"><i class="bi bi-cash-coin" style="font-size: 2rem;"></i></div>
                            <div class="m-2 icon-item" data-icon="piggy-bank-fill"><i class="bi bi-piggy-bank-fill" style="font-size: 2rem;"></i></div>
                            <div class="m-2 icon-item" data-icon="google"><i class="bi bi-google" style="font-size: 2rem;"></i></div>
                            <div class="m-2 icon-item" data-icon="paypal"><i class="bi bi-paypal" style="font-size: 2rem;"></i></div>
                            <div class="m-2 icon-item" data-icon="airplane-fill"><i class="bi bi-airplane-fill" style="font-size: 2rem;"></i></div>
                            <div class="m-2 icon-item" data-icon="bus-front-fill"><i class="bi bi-bus-front-fill" style="font-size: 2rem;"></i></div>
                            <div class="m-2 icon-item" data-icon="car-front-fill"><i class="bi bi-car-front-fill" style="font-size: 2rem;"></i></div>
                            <div class="m-2 icon-item" data-icon="gift-fill"><i class="bi bi-gift-fill" style="font-size: 2rem;"></i></div>
                            <div class="m-2 icon-item" data-icon="cloud-fill"><i class="bi bi-cloud-fill" style="font-size: 2rem;"></i></div>
                            <div class="m-2 icon-item" data-icon="controller"><i class="bi bi-controller" style="font-size: 2rem;"></i></div>
                            <div class="m-2 icon-item" data-icon="credit-card-fill"><i class="bi bi-credit-card-fill" style="font-size: 2rem;"></i></div>
                            <div class="m-2 icon-item" data-icon="cup-hot-fill"><i class="bi bi-cup-hot-fill" style="font-size: 2rem;"></i></div>
                            <div class="m-2 icon-item" data-icon="currency-dollar"><i class="bi bi-currency-dollar" style="font-size: 2rem;"></i></div>
                            <div class="m-2 icon-item" data-icon="currency-euro"><i class="bi bi-currency-euro" style="font-size: 2rem;"></i></div>
                            <div class="m-2 icon-item" data-icon="currency-pound"><i class="bi bi-currency-pound" style="font-size: 2rem;"></i></div>
                            <div class="m-2 icon-item" data-icon="ev-station-fill"><i class="bi bi-ev-station-fill" style="font-size: 2rem;"></i></div>
                            <div class="m-2 icon-item" data-icon="hammer"><i class="bi bi-hammer" style="font-size: 2rem;"></i></div>
                            <div class="m-2 icon-item" data-icon="house-fill"><i class="bi bi-house-fill" style="font-size: 2rem;"></i></div>
                            <div class="m-2 icon-item" data-icon="mailbox2"><i class="bi bi-mailbox2" style="font-size: 2rem;"></i></div>
                            <div class="m-2 icon-item" data-icon="person-fill"><i class="bi bi-person-fill" style="font-size: 2rem;"></i></div>
                            <div class="m-2 icon-item" data-icon="ticket-perforated-fill"><i class="bi bi-ticket-perforated-fill" style="font-size: 2rem;"></i></div>
                        </div>
                        <!-- Hidden input to store the selected icon value -->
                        <input type="hidden" name="icon" id="selectedIcon" required>
                    </div>

					<!-- Show in dashboard flag -->
					<div class="form-group form-check mb-3">
						<input type="checkbox" name="show_in_dashboard" class="form-check-input" id="showInDashboard" <?php echo $disableDashboard; ?>>
						<label class="form-check-label <?php echo $dashboardLabelClass; ?>" for="showInDashboard">
							Mostra in dashboard
						</label>
						<?php if ($maxDashboardCount >= 5): ?>
							<small class="text-muted d-block">Massimo 5 portafogli in dashboard</small>
						<?php endif; ?>
					</div>

                    <button type="submit" class="btn btn-primary w-100">Aggiungi</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Style for clickable icon items -->
<style>
    .icon-item {
        cursor: pointer;
        border: 2px solid transparent;
        padding: 0.5rem;
        border-radius: 0.25rem;
    }
    .icon-item.selected {
        border-color: #0d6efd;
        background-color: #0d6efd;
    }
</style>

<script>
    $(document).ready(function() {
        // Icon selection handler
        $('#iconSelection .icon-item').click(function() {
            $('#iconSelection .icon-item').removeClass('selected');
            $(this).addClass('selected');
            $('#selectedIcon').val($(this).data('icon'));
        });

        $('#addWalletForm').submit(function(e) {
            e.preventDefault();
            $('button[type="submit"]').prop('disabled', true);
            
            $.ajax({
                url: 'addWallet.php',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if(response.status === 'success') {
                        $('#addWalletStatus').html('<div class="alert alert-success">' + response.message + '</div>');
                        $('#addWalletForm')[0].reset();
                        // Remove selected class from icons after reset
                        $('#iconSelection .icon-item').removeClass('selected');
                        setTimeout(function() { window.location.reload(); }, 1000);
                    } else {
                        $('#addWalletStatus').html('<div class="alert alert-danger">' + response.message + '</div>');
                    }
                    $('button[type="submit"]').prop('disabled', false);
                },
                error: function() {
                    $('#addWalletStatus').html('<div class="alert alert-danger">Qualcosa è andato storto. Riprova più tardi.</div>');
                    $('button[type="submit"]').prop('disabled', false);
                }
            });
        });
    });
</script>

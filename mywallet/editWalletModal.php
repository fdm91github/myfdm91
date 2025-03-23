<!-- Modale per la modifica di un portafoglio -->
<div class="modal fade" id="editWalletModal" tabindex="-1" aria-labelledby="editWalletModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg"> <!-- enlarged modal for icon grid -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editWalletModalLabel">Modifica Portafoglio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="editWalletStatus"></div>
                <form id="editWalletForm">
                    <input type="hidden" name="id" id="editWalletId">
                    <div class="form-group mb-3">
                        <label for="editWalletDescription">Descrizione</label>
                        <input type="text" name="description" id="editWalletDescription" class="form-control" required>					
                    </div>
                    
                    <div class="form-group mb-3">
                        <label>Icona</label>
                        <div class="d-flex flex-wrap" id="editIconSelection">
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
                        <input type="hidden" name="icon" id="editSelectedIcon" required>
                    </div>

                    <div class="form-group form-check mb-3">
                        <input type="checkbox" name="show_in_dashboard" class="form-check-input" id="editShowInDashboard">
                        <label class="form-check-label" for="editShowInDashboard">
                            Mostra in dashboard
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Aggiorna</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    /* Styling per la selezione delle icone */
    #editIconSelection .icon-item {
        cursor: pointer;
        border: 2px solid transparent;
        padding: 0.5rem;
        border-radius: 0.25rem;
    }
    #editIconSelection .icon-item.selected {
        border-color: #0d6efd;
        background-color: #0d6efd;
    }
</style>

<script>
    $(document).ready(function() {
        // Al momento dell'apertura della modale, pre-compila i campi con i dati correnti
        $('#editWalletModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var id = button.data('id');
            var description = button.data('description');
            var icon = button.data('icon');
            var showInDashboard = button.data('show_in_dashboard');

            $('#editWalletId').val(id);
            $('#editWalletDescription').val(description);

            // Seleziona l'icona correntemente impostata
            $('#editIconSelection .icon-item').removeClass('selected');
            $('#editIconSelection .icon-item[data-icon="' + icon + '"]').addClass('selected');
            $('#editSelectedIcon').val(icon);

            // Imposta lo stato del checkbox
            if(showInDashboard == 1){
                $('#editShowInDashboard').prop('checked', true);
            } else {
                $('#editShowInDashboard').prop('checked', false);
            }
        });

        // Gestore della selezione dell'icona nel modal di modifica
        $('#editIconSelection .icon-item').click(function() {
            $('#editIconSelection .icon-item').removeClass('selected');
            $(this).addClass('selected');
            $('#editSelectedIcon').val($(this).data('icon'));
        });

        $('#editWalletForm').submit(function(e) {
            e.preventDefault();

            $.ajax({
                url: 'editWallet.php?id=' + $('#editWalletId').val(),
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if(response.status === 'success') {
                        $('#editWalletStatus').html('<div class="alert alert-success">' + response.message + '</div>');
                        setTimeout(function() { window.location.reload(); }, 1000);
                    } else {
                        $('#editWalletStatus').html('<div class="alert alert-danger">' + response.message + '</div>');
                    }
                },
                error: function() {
                    $('#editWalletStatus').html('<div class="alert alert-danger">Qualcosa è andato storto. Riprova più tardi.</div>');
                }
            });
        });
    });
</script>

<!-- Modale per l'il ripristino di un Portafogli -->
<div class="modal fade" id="restoreWalletModal" tabindex="-1" aria-labelledby="restoreWalletModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="restoreWalletModalLabel">Ripristina veicolo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="restoreWalletStatus"></div>
                <p>Sei sicuro di voler ripristinare questo veicolo?</p>
                <form id="restoreWalletForm" method="POST" action="restoreWallet.php">
                    <input type="hidden" name="id" id="restoreWalletId">
                    <button type="submit" class="btn btn-primary w-100">Ripristina</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
		$('#restoreWalletModal').on('show.bs.modal', function (event) {
			var button = $(event.relatedTarget);
			var id = button.data('id');

			$('#restoreWalletId').val(id);
		});

        $('#restoreWalletForm').submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: 'restoreWallet.php',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    $('#restoreWalletStatus').html('<div class="alert alert-success">Veicolo ripristinato con successo.</div>');
                    setTimeout(function() { window.location.reload(); }, 1000);
                },
                error: function() {
                    $('#restoreWalletStatus').html('<div class="alert alert-danger">Qualcosa è andato storto. Riprova più tardi.</div>');
                }
            });
		});
    });
</script>

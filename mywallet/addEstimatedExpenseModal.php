<!-- Modale per l'aggiunta di una spesa stimata -->
<div class="modal fade" id="addEstimatedExpenseModal" tabindex="-1" aria-labelledby="addEstimatedExpenseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addEstimatedExpenseModalLabel">Aggiungi spesa stimata</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="addEstimatedExpenseStatus"></div>
                <form id="addEstimatedExpenseForm">
                    <input type="hidden" name="id" id="editEstimatedExpenseId">
                    <div class="form-group">
                        <label for="addEstimatedExpenseName">Descrizione</label>
                        <input type="text" name="name" id="addEstimatedExpenseName" class="form-control" placeholder="Descrizione" required>
                    </div>
                    <div class="form-group">
                        <label for="addEstimatedExpenseAmount">Totale</label>
                        <input type="number" name="amount" id="addEstimatedExpenseAmount" class="form-control" step="0.01" placeholder="Totale" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="addEstimatedExpenseStartMonth">Mese di inizio</label>
                            <select name="start_month" id="addEstimatedExpenseStartMonth" class="form-control" required>
                                <option value="" disabled selected>Seleziona il mese</option>
                                <option value="1">Gennaio</option>
                                <option value="2">Febbraio</option>
                                <option value="3">Marzo</option>
                                <option value="4">Aprile</option>
                                <option value="5">Maggio</option>
                                <option value="6">Giugno</option>
                                <option value="7">Luglio</option>
                                <option value="8">Agosto</option>
                                <option value="9">Settembre</option>
                                <option value="10">Ottobre</option>
                                <option value="11">Novembre</option>
                                <option value="12">Dicembre</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="addEstimatedExpenseStartYear">Anno di inizio</label>
                            <input type="number" name="start_year" id="addEstimatedExpenseStartYear" class="form-control" min="1900" max="2100" required>
                        </div>
                    </div>
                    <div id="addEstimatedExpenseEndDateFields" class="form-row">
                        <div class="form-group col-md-6">
                            <label for="addEstimatedExpenseEndMonth">Mese di fine</label>
                            <select name="end_month" id="addEstimatedExpenseEndMonth" class="form-control">
                                <option value="" disabled selected>Seleziona il mese</option>
                                <option value="1">Gennaio</option>
                                <option value="2">Febbraio</option>
                                <option value="3">Marzo</option>
                                <option value="4">Aprile</option>
                                <option value="5">Maggio</option>
                                <option value="6">Giugno</option>
                                <option value="7">Luglio</option>
                                <option value="8">Agosto</option>
                                <option value="9">Settembre</option>
                                <option value="10">Ottobre</option>
                                <option value="11">Novembre</option>
                                <option value="12">Dicembre</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="addEstimatedExpenseEndYear">Anno di fine</label>
                            <input type="number" name="end_year" id="addEstimatedExpenseEndYear" class="form-control" min="1900" max="2100">
                        </div>
                    </div>
                    <div class="form-group form-check">
                        <input type="checkbox" name="undetermined" id="addEstimatedExpenseUndetermined" class="form-check-input" onclick="toggleEstimatedEndDate('add')">
                        <label class="form-check-label" for="addEstimatedExpenseUndetermined">Indeterminato</label>
                    </div>
                    <div class="form-group">
                        <label for="addEstimatedExpenseDebitDate">Data di addebito</label>
                        <input type="number" name="debit_date" id="addEstimatedExpenseDebitDate" class="form-control" min="1" max="31" required>
                    </div>
                    <div class="form-group">
                        <label for="addEstimatedExpenseBillingFrequency">Frequenza di fatturazione (mesi)</label>
                        <input type="number" name="billing_frequency" id="addEstimatedExpenseBillingFrequency" class="form-control" min="1" value="1" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100" id="addEstimatedExpenseSubmitButton">Aggiungi</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
	$(function() {
		// Funzione che toggla wrapper + disable campi
		function toggleEndDate() {
		  var checked = $('#addEstimatedExpenseUndetermined').is(':checked');
		  $('#addEstimatedExpenseEndDateFields')
			.toggleClass('d-none', checked)
			.find('select, input').prop('disabled', checked);
		}

		// Imposta date di default
		(function setDefaults(){
		  var today = new Date();
		  var m = today.getMonth() + 1, y = today.getFullYear();
		  $('#addEstimatedExpenseStartMonth').val(m);
		  $('#addEstimatedExpenseStartYear').val(y);
		  $('#addEstimatedExpenseEndMonth').val(m);
		  $('#addEstimatedExpenseEndYear').val(y);
		})();

		// All’apertura del modal: sincronizza subito lo stato
		$('#addEstimatedExpenseModal').on('shown.bs.modal', toggleEndDate);

		// Al cambio del checkbox
		$('#addEstimatedExpenseUndetermined').on('change', toggleEndDate);

		// Inizializza anche al caricamento della pagina (utile se il checkbox fosse pre-spuntato)
		toggleEndDate();
	});

	document.addEventListener('DOMContentLoaded', function() {
		// Imposta date di default…
		var today = new Date();
		var currentMonth = today.getMonth() + 1;
		var currentYear  = today.getFullYear();

		document.getElementById('addEstimatedExpenseStartMonth').value = currentMonth;
		document.getElementById('addEstimatedExpenseStartYear').value  = currentYear;
		document.getElementById('addEstimatedExpenseEndMonth').value   = currentMonth;
		document.getElementById('addEstimatedExpenseEndYear').value    = currentYear;

		// Applica subito lo stato “undetermined” (se già spuntato)
		toggleEstimatedEndDate('add');
	});

	$(document).ready(function() {
		$('#addEstimatedExpenseUndetermined').on('change', function() {
		  toggleEstimatedEndDate('add');
		});
		
        $('#addEstimatedExpenseForm').submit(function(e) {
            e.preventDefault();

            var startMonth = parseInt($('#addEstimatedExpenseStartMonth').val());
            var startYear = parseInt($('#addEstimatedExpenseStartYear').val());
            var endMonth = parseInt($('#addEstimatedExpenseEndMonth').val());
            var endYear = parseInt($('#addEstimatedExpenseEndYear').val());
            var billingFrequency = parseInt($('#addEstimatedExpenseBillingFrequency').val());
            var undetermined = $('#addEstimatedExpenseUndetermined').prop('checked');

			// Controllo che la data di inizio sia antecedente a quella di fine
            if (!undetermined && (endYear < startYear || (endYear === startYear && endMonth < startMonth))) {
                $('#addEstimatedExpenseStatus').html('<div class="alert alert-danger">La data di fine non può essere antecedente alla data di inizio.</div>');
                return;
            }

			// Controllo che la frequenza di fatturazione sia congruente con i mesi inseriti
            if (!undetermined) {
                var totalMonths = (endYear - startYear) * 12 + (endMonth - startMonth +1);
                if (totalMonths < 0 || totalMonths % billingFrequency !== 0) {
                    $('#addEstimatedExpenseStatus').html('<div class="alert alert-danger">La data di fine non combacia con la frequenza di fatturazione.</div>');
                    return;
                }
            }

            $.ajax({
                url: 'addEstimatedExpense.php',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    $('#addEstimatedExpenseStatus').html('<div class="alert alert-success">Spesa aggiunta con successo!</div>');
                    $('#addEstimatedExpenseForm')[0].reset();
                    setTimeout(function() { window.location.reload(); }, 1000);
                },
                error: function() {
                    $('#addEstimatedExpenseStatus').html('<div class="alert alert-danger">Qualcosa è andato storto. Riprova più tardi.</div>');
                }
            });
        });
    });
</script>

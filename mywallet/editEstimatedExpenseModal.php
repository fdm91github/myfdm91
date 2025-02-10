<!-- Modale per la modifica di una spesa stimata -->
<div class="modal fade" id="editEstimatedExpenseModal" tabindex="-1" role="dialog" aria-labelledby="editEstimatedExpenseModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editEstimatedExpenseModalLabel">Modifica spesa stimata</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="editEstimatedExpenseStatus"></div>
                <form id="editEstimatedExpenseForm">
                    <input type="hidden" name="id" id="editEstimatedExpenseId">
                    <div class="form-group">
                        <label for="editEstimatedExpenseName">Descrizione</label>
                        <input type="text" name="name" id="editEstimatedExpenseName" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="editEstimatedExpenseAmount">Totale</label>
                        <input type="number" name="amount" id="editEstimatedExpenseAmount" class="form-control" step="0.01" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="editEstimatedExpenseStartMonth">Mese di inizio</label>
                            <select name="start_month" id="editEstimatedExpenseStartMonth" class="form-control" required>
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
                            <label for="editEstimatedExpenseStartYear">Anno di inizio</label>
                            <input type="number" name="start_year" id="editEstimatedExpenseStartYear" class="form-control" min="1900" max="2100" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="editEstimatedExpenseEndMonth">Mese di fine</label>
                            <select name="end_month" id="editEstimatedExpenseEndMonth" class="form-control">
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
                            <label for="editEstimatedExpenseEndYear">Anno di fine</label>
                            <input type="number" name="end_year" id="editEstimatedExpenseEndYear" class="form-control" min="1900" max="2100">
                        </div>
                    </div>
                    <div class="form-group form-check">
                        <input type="checkbox" name="undetermined" id="editEstimatedExpenseUndetermined" class="form-check-input" onclick="toggleEstimatedEndDate('edit')">
                        <label class="form-check-label" for="editEstimatedExpenseUndetermined">Indeterminato</label>
                    </div>
                    <div class="form-group">
                        <label for="editEstimatedExpenseDebitDate">Data di addebito</label>
                        <input type="number" name="debit_date" id="editEstimatedExpenseDebitDate" class="form-control" min="1" max="31" required>
                    </div>
                    <div class="form-group">
                        <label for="editEstimatedExpenseBillingFrequency">Frequenza di fatturazione (mesi)</label>
                        <input type="number" name="billing_frequency" id="editEstimatedExpenseBillingFrequency" class="form-control" min="1" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Aggiorna</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleEstimatedEndDate(action) {
        var endMonth = document.getElementById(action + 'EstimatedExpenseEndMonth');
        var endYear = document.getElementById(action + 'EstimatedExpenseEndYear');
        if (document.getElementById(action + 'EstimatedExpenseUndetermined').checked) {
            endMonth.disabled = true;
            endYear.disabled = true;
        } else {
            endMonth.disabled = false;
            endYear.disabled = false;
        }
    }

    $(document).ready(function() {
        $('#editEstimatedExpenseModal').on('show.bs.modal', function (event) {
			var button = $(event.relatedTarget);
			var id = button.data('id');
			$('#editEstimatedExpenseId').val(id);
			var name = button.data('name');
			var amount = button.data('amount');
			var start_month = button.data('start-month');
			var start_year = button.data('start-year');
			var end_month = button.data('end-month');
			var end_year = button.data('end-year');
			var undetermined = button.data('undetermined'); 
			var debit_date = button.data('debit-date');
			var billing_frequency = button.data('billing-frequency');
			
			var modal = $(this);
			modal.find('#editEstimatedExpenseId').val(id);
			modal.find('#editEstimatedExpenseName').val(name);
			modal.find('#editEstimatedExpenseAmount').val(amount);
			modal.find('#editEstimatedExpenseStartMonth').val(start_month);
			modal.find('#editEstimatedExpenseStartYear').val(start_year);
			modal.find('#editEstimatedExpenseEndMonth').val(end_month);
			modal.find('#editEstimatedExpenseEndYear').val(end_year);
			modal.find('#editEstimatedExpenseUndetermined').prop('checked', undetermined);
			modal.find('#editEstimatedExpenseDebitDate').val(debit_date);
			modal.find('#editEstimatedExpenseBillingFrequency').val(billing_frequency);
        });

        $('#editEstimatedExpenseForm').submit(function(e) {
            e.preventDefault();			
            var startMonth = parseInt($('#editEstimatedExpenseStartMonth').val());
            var startYear = parseInt($('#editEstimatedExpenseStartYear').val());
            var endMonth = parseInt($('#editEstimatedExpenseEndMonth').val());
            var endYear = parseInt($('#editEstimatedExpenseEndYear').val());
            var billingFrequency = parseInt($('#editEstimatedExpenseBillingFrequency').val());
            var undetermined = $('#editEstimatedExpenseUndetermined').prop('checked');

			// Controllo che la data di inizio sia antecedente a quella di fine
            if (!undetermined && (endYear < startYear || (endYear === startYear && endMonth < startMonth))) {
                $('#editEstimatedExpenseStatus').html('<div class="alert alert-danger">La data di fine non può essere antecedente alla data di inizio.</div>');
                return;
            }

			// Controllo che la frequenza di fatturazione sia congruente con i mesi inseriti
            if (!undetermined) {
                var totalMonths = (endYear - startYear) * 12 + (endMonth - startMonth +1);
                if (totalMonths < 0 || totalMonths % billingFrequency !== 0) {
                    $('#editEstimatedExpenseStatus').html('<div class="alert alert-danger">La data di fine non combacia con la frequenza di fatturazione.</div>');
                    return;
                }
            }

            $.ajax({
                url: 'editEstimatedExpense.php?id=' + $('#editEstimatedExpenseId').val(),
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    $('#editEstimatedExpenseStatus').html('<div class="alert alert-success">Spesa modificata con successo!</div>');
                    setTimeout(function() { window.location.reload(); }, 1000);
                },
                error: function() {
                    $('#editEstimatedExpenseStatus').html('<div class="alert alert-danger">Qualcosa è andato storto. Riprova più tardi.</div>');
                }
            });
        });
    });
</script>

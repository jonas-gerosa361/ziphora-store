@extends('templates.external')
@section('content')
<form id="formData" method="POST" action="/tickets">
    @csrf
    <div class="d-flex justify-content-center">
        <label class="mt-2"><strong>Filtrar chamados: &nbsp;</strong></label>
    <select id='situation' autocomplete="off" name="situation" onchange="filterTicket(event)" class="form-control" style="width: 200px">
            <option value="Em andamento">Em andamento</option>
            <option value="Finalizado">Finalizado</option>
            <option value="Todos">Todos</option>
        </select>
    </div>
</form>
<table class="mt-2 table table-striped">
    <thead>
        <tr>
            <th>#</th>
            <th>Aberto em</th>
            <th>Assunto</th>
            <th>Descrição</th>
            <th>Situação</th>
            <th>Status</th>
            <th>Aberto por</th>
            <th>Ação</th>
        </tr>
    </thead>
    <tbody>  
        
    </tbody>

</table>
<div class="d-flex flex-row-reverse">
    <a href="/tickets/ticket/create">
        <button class="btn btn-primary">Novo chamado</button>
    </a>
</div>



<script>

</script>

@endsection

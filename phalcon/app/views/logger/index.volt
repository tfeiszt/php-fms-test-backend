<div class="page-header">
    <h1>Log</h1>
</div>

<div class="page-content">
    <div class="col-lg-12">
        <table class="table table-striped">
            <thead>
                <tr><th>Method</th><th>Type</th><th>Name</th><th>Time</th></tr>
            </thead>
            <tbody>
                {% for log in logs %}

                    <tr><td>{{log.method}}</td><td>{{log.type}}</td><td>{{log.objname}}</td><td>{{log.created_at}}</td></tr>

                {% endfor %}
            </tbody>

        </table>
    </div>
</div>
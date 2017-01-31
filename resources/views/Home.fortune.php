<% extends("Master") %>

<% part("content") %>
<div id="main-wrapper">
    <section id="newTask">
        <h2>Add Task</h2>
        <form action="{{! route('addTask') !}}" method="POST">
            {{! csrfInput() !}}
            <input type="text" placeholder="Text" name="text">
            <button type="submit">Add</button>
        </form>
    </section>
    <section id="currTasks">
        <h2>Current Tasks</h2>
        <% forif ($tasks as $task) %>
        <article class="task">
            <h3>{{ $task->getText() }}</h3>
            <form action="{{! route('deleteTask', [$task->getId()]) !}}" method="POST">
                {{! httpMethodInput("DELETE") !}}
                <button type="submit">Delete</button>
            </form>
        </article>
        <% forelse %>
        There are no tasks yet
        <% endif %>
    </section>
</div>
<% endpart %>
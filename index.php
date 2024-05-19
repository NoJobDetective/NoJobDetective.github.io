<?php

mb_http_output('UTF-8');
header('Content-type: text/html; charset=UTF-8');

// タスクの取得
$tasks = array();
$task_lines = file('todos.txt', FILE_IGNORE_NEW_LINES);
foreach ($task_lines as $line) {
    $parts = explode('|', $line);
    $task = array(
        'text' => $parts[0],
        'completed' => count($parts) > 1 && $parts[1] === 'completed'
    );
    $tasks[] = $task;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Todo List</title>
    <style>
        .todo-item {
            cursor: move;
            padding: 10px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            margin-bottom: 10px;
        }

.todo-input {
  width: 250px;
  height: 30px;
  padding: 5px;
  border: 1px solid #ccc;
  border-radius: 5px;
  font-size: 16px;
}


.todo-input::placeholder {
  color: #999; /* プレースホルダの色 */
}


.todo-input:focus {
  outline: none; /* アウトラインを非表示 */
  border-color: #007bff; /* フォーカス時の境界線の色 */
  box-shadow: 0 0 5px rgba(0, 123, 255, 0.2); /* ボックスシャドウ */
}

    </style>
</head>
<body translate="no">
    <h1>Todo List</h1>
    <form id="taskForm" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <input id="task-input" class="todo-input" type="text" name="task" placeholder="新しいタスクを入力">
        <button type="submit">追加</button>
	<input id="input-deadline" class="todo-input" name="deadline" type="date"/><label for="deadline">まで
	<input type="radio" name="inputPriority" value="高"/><label for="inputPriority">高</label>
	<input type="radio" name="inputPriority" value="中"/><label for="inputPriority">中</label>
	<input type="radio" name="inputPriority" value="低"/><label for="inputPriority">低</label>

	<input type="checkbox" name="inputAgenda"/><label for="inputAgenda">議題</label>
	<input type="checkbox" name="inputWait"/><label for="inputWait">待機中</label>
	<input type="checkbox" name="inputWorking"/><label for="inputWorking">作業中</label>
	<input type="checkbox" name="inputConfirmation"/><label for="inputConfirmation">要確認</label>
</label>
    </form>
<ul id="todo-list">
    <?php foreach ($tasks as $task): ?>
    <li class="todo-item" draggable="true" data-task="<?php echo htmlspecialchars($task['text']); ?>">
        <input type="checkbox" class="todo-checkbox" data-task="<?php echo htmlspecialchars($task['text']); ?>" <?php echo $task['completed'] ? 'checked' : ''; ?>>
        <span class="todo-text" contenteditable="true" data-original="<?php echo htmlspecialchars($task['text']); ?>"><?php echo $task['text']; ?></span>
        <button class="save-btn">保存</button>
        <button class="delete-btn">削除</button>
    </li>
    <?php endforeach; ?>
</ul>
    <script>

const inputElement = document.getElementById('task-input');
inputElement.focus();

// タスクの追加処理
const taskForm = document.getElementById('taskForm');
taskForm.addEventListener('submit', (event) => {
  event.preventDefault();

  const task = event.target.elements.task.value.trim();
  if (!task) return;

  var deadline = event.target.elements.deadline.value.trim();

  if(deadline != ""){
    var deadlineText = " 【" + deadline + "まで】";
  }else{
    var deadlineText = "";
  }

  var priority = document.querySelector('input[name="inputPriority"]:checked');

  if(priority){
    var priorityText = " 優先度:" + priority.value;
  }else{
    var priorityText = "";
  }

  var agenda = document.querySelector('input[name="inputAgenda"]:checked');

  if(agenda){
    var agendaText = " 【議題】";
  }else{
    var agendaText = "";
  }

  var wait = document.querySelector('input[name="inputWait"]:checked');

  if(wait){
    var waitText = " 【待機中】";
  }else{
    var waitText = "";
  }

  var working = document.querySelector('input[name="inputWorking"]:checked');

  if(working){
    var workingText = " 【作業中】";
  }else{
    var workingText = "";
  }

  var confirmation = document.querySelector('input[name="inputConfirmation"]:checked');

  if(confirmation){
    var confirmationText = " 【要確認】";
  }else{
    var confirmationText = "";
  }

  fetch('submit.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded'
    },
    body: 'task=' + encodeURIComponent(agendaText + waitText + workingText + confirmationText + task + priorityText + deadlineText)
  }).then(() => {
    window.location.reload();
  });
});

const todoList = document.getElementById('todo-list');
let draggedItem = null;

let currentItem = null;

const todoItems = document.querySelectorAll('.todo-item');

todoItems.forEach(item => {
    const todoText = item.querySelector('.todo-text');
    const saveBtn = item.querySelector('.save-btn');
    const deleteBtn = item.querySelector('.delete-btn');
    const checkbox = item.querySelector('.todo-checkbox');

    if (todoText && saveBtn && deleteBtn && checkbox) {
        saveBtn.addEventListener('click', function() {
            const originalText = todoText.dataset.original;
            const newText = todoText.textContent.trim();
            if (newText !== originalText) {
                const taskText = item.dataset.task;
                fetch('update.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'task=' + encodeURIComponent(originalText) + '&new_text=' + encodeURIComponent(newText)
                })
                .then(response => response.text())
                .then(data => {
                    if (data !== 'success') {
                        console.error('Error updating task text:', data);
                        todoText.textContent = originalText;
                        window.location.reload();
                    } else {
                        todoText.dataset.original = newText;
                    }
                })
                .catch(error => {
                    console.error('Error updating task text:', error);
                    todoText.textContent = originalText;
                });
            }
        });

        deleteBtn.addEventListener('click', function() {
            const taskText = item.dataset.task;
            fetch('delete.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'task=' + encodeURIComponent(taskText)
            })
            .then(response => response.text())
            .then(data => {
                if (data === 'success') {
                    item.remove();
                    window.location.reload();
                } else {
                    console.error('Error deleting task:', data);
                }
            })
            .catch(error => {
                console.error('Error deleting task:', error);
            });
        });

        checkbox.addEventListener('change', function() {
            const itemText = item.dataset.task;
            const isCompleted = checkbox.checked;

            fetch('update_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'task=' + encodeURIComponent(itemText) + '&completed=' + encodeURIComponent(isCompleted)
            })
            .then(response => response.text())
            .then(data => {
                if (data !== 'success') {
                    console.error('Error updating task status:', data);
                }
            })
            .catch(error => {
                console.error('Error updating task status:', error);
            });
        });
    }
});

        todoList.addEventListener('dragstart', function(e) {
            draggedItem = e.target;
            e.dataTransfer.effectAllowed = 'move';
        });

        todoList.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
        });

todoList.addEventListener('drop', function(e) {
    e.preventDefault();
    const target = e.target.closest('li');
    if (target && target !== draggedItem) {
        const items = Array.from(todoList.children);
        const sourceIndex = items.indexOf(draggedItem);
        const targetIndex = items.indexOf(target);

        // 移動元のタスクの完了状態を取得
        const sourceCompleted = draggedItem.querySelector('.todo-checkbox').checked;

        items.splice(targetIndex, 0, items.splice(sourceIndex, 1)[0]);

        // 移動先のタスクに完了状態を設定
        const targetCheckbox = items[targetIndex].querySelector('.todo-checkbox');
        targetCheckbox.checked = sourceCompleted;

        items.forEach(item => todoList.appendChild(item));

        // テキストファイルの更新
        const tasks = items.map(item => {
            const itemText = item.querySelector('.todo-text').textContent;
            const completed = item.querySelector('.todo-checkbox').checked;
            return `${itemText}${completed ? '|completed' : ''}`;
        });
        const tasksString = tasks.join('\n');
        fetch('move.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'tasks=' + encodeURIComponent(tasksString)
        });
    }
});

    </script>
</body>
</html>

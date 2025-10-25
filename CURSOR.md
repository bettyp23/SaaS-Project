# cursor.md — Project Plan & Workflow

## 1. Initial Analysis
- Read the codebase and note relevant files.  
- Write a clear plan to `todo.md`.

## 2. Design Inspiration
- Use the `design/` folder **only** for visual reference.  
- **Do not modify** anything inside it.

## 3. Todo List Structure
- Add checkable tasks in `todo.md`.  
- Mark items complete as you go.  
- Add a final “Review” section summarizing changes.

## 4. Verification
- Wait for my approval before starting; I will verify the plan.

## 5. Task Execution
- After approval, begin executing tasks by working on the todo items.  
- Mark items as complete as you go.

## 6. Communication
- After each task, give a high-level explanation:  
  - What changed  
  - Why  
  - Commit hash

## 7. Simplicity Principle
- Avoid big or complex edits.  
- Every change should affect as little code as possible.

## 8. Process Documentation
- Append each and every action to `docs/activity.md`.  
- Read that file whenever you find it necessary to assist you.  
- Include every prompt I give:  
  - Timestamp  
  - Files changed  
  - Commit  
  - Prompt  
  - Result  

## 9. Git Rules
- Push only after every successful change.

## 10. HTML Folder
- The `html` folder is the HTML home directory of the web server.  
- It is a traditional LAMP stack.  
- All deployable files must be under `html/` or a subfolder.

## 11. ID Tags
- Every `<div>` must have a unique ID to communicate if needed to make style changes.  
- Use the format: `cursor-<page>-<purpose>-<num>`  
  - e.g., `cursor-index-hero-1`

## 12. Review
At the end of `todo.md`, include:  
- Summary of completed tasks and changes  
- File changes + commit hashes  
- Next steps

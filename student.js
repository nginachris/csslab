let name = prompt("Enter your name:");
let course = prompt("Enter your course:");
let year = prompt("Enter your year of study:");

document.write("<h2>Student Information</h2>");
document.write("Name: " + name + "<br>");
document.write("Course: " + course + "<br>");
document.write("Year of Study: " + year + "<br><br>");

if (year == 4) {
    document.write("<strong>🎉 Congratulations! You are a Year 4 student. Wishing you success in your final year and future career!</strong>");
} else {
    document.write("<strong>Keep studying hard. All the best in your academic journey!</strong>");
}
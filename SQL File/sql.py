import random

def generate_marks():
    return {
        'CT_1': random.randint(0, 20),
        'CT_2': random.randint(0, 20),
        'CT_3': random.randint(0, 20),
        'CT_4': random.randint(0, 20),
        'Attendance': random.randint(0, 10),
        'Assignment': random.randint(0, 10),
        'Semester_Final': random.randint(30, 60),
        'Quiz': random.randint(0, 20),
        'BoardViva': random.randint(15, 25),
        'Performance': random.randint(30, 45)
    }

students = range(2010001, 2010062)
courses = ['ECE-1201', 'ECE-1202', 'ECE-1203', 'ECE-1204',]  # Replace with other course codes.

for student in students:
    for course in courses:
        marks = generate_marks()
        print(f"INSERT INTO tblmarks (RollId, Semester, CourseCode, CT_1, CT_2, CT_3, CT_4, Attendance, Assignment, Semester_Final) "
              f"VALUES ({student}, '2', '{course}', {marks['CT_1']}, {marks['CT_2']}, {marks['CT_3']}, {marks['CT_4']}, "
              f"{marks['Attendance']}, {marks['Assignment']}, {marks['Semester_Final']});")
        print(f"INSERT INTO tblsessional (RollId, Semester, CourseCode, Attendance, Quiz, BoardViva, Performance) "
              f"VALUES ({student}, '2', '{course}', {marks['Attendance']}, {marks['Quiz']}, {marks['BoardViva']}, {marks['Performance']});")

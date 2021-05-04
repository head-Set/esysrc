const emailRules = (email) => {
  let tester = /^[A-Z0-9._%+-]+@([A-Z0-9-]+\.)+[A-Z]{2,4}$/i;
  if (tester.test(email)) return true;
  return false;
};
const blankChecker = (items) => {
  let isValid = true;
  items.forEach((el) => {
      if (el == null || el == "") isValid = false;
  });
  return isValid;
};
export {
  emailRules,
  blankChecker
}
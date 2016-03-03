

import java.io.Serializable;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.LinkedHashMap;
import java.util.Map;
import java.util.Map.Entry;
import java.util.Scanner;
import java.util.Set;
import java.lang.Object;

public class HashMapSample {
	
	protected static LinkedHashMap<String, Serializable> inputMap = new LinkedHashMap<String, Serializable>(); 
	
	public static void main(String[] args) {
        Scanner scanner;
        String digit;
        int input = 0;        
		while (true) {
			inputMap = new LinkedHashMap<String, Serializable>();
			System.out.println("Enter a digit: ");	
			scanner = new Scanner(System.in);
			digit = scanner.nextLine();
			if (isNumeric(digit)) {
				if(Integer.parseInt(digit)%2 == 1){
					input = Integer.parseInt(digit);
					System.out.println("Your input is " + input);
			        verticalPrint(input);        
			        horizontalPrint(inputMap);			        
			        if(quitProgram()){
			        	break;
			        }
				} else {
					System.out.println("Your input should be an odd number, try again");
				}
			} else {
				System.out.println("This is not a digit, try Again");
			}
		}
        scanner.close();

	}	
	
	public static boolean quitProgram(){
		System.out.println("Press 1 to try again. Press 0 to quit.");
		Scanner scanner;
		boolean isQuit = false;
		while(true){
			scanner = new Scanner(System.in);
			String digit = scanner.nextLine();
			if (isNumeric(digit)) {
				if(Integer.parseInt(digit) == 0){
					isQuit = true;
					System.out.println("Successfully closed the program!");
					break;
				}
				else if (Integer.parseInt(digit) == 1){
					isQuit = false;
					break;
				}
				else
					System.out.println("Incorrect input");
			} else {
				System.out.println("This is not a digit, try Again");
			}
		}
		//scanner.close();
		return isQuit;
	}
	
	public static void drawZ(int input_i){
		int decreaser = 1;	
		String sLine;
		for(int x = 0; x < input_i; x++){	
			sLine = "";
			for(int y = 0; y < input_i ; y++){
				if(y == input_i-decreaser){
					sLine+="*";
					System.out.print("*");
				}
				else if (x == 0 || x == input_i-1){
					sLine+="*";
					System.out.print("*");
				}
				else{
					sLine+=" ";
					System.out.print(" ");	
				}
			}
			if(inputMap.containsKey(String.valueOf(x))){
				inputMap.put(String.valueOf(x), inputMap.get(String.valueOf(x))+" "+sLine);
			} else {
				inputMap.put(String.valueOf(x), sLine);
			}
			decreaser++;
			System.out.print("\n");
		}
	}
	
	public static void drawX(int input_i){
		int decreaser = 1;
		String sLine;
		for(int x = 0; x < input_i; x++){		
			sLine = "";
			for(int y = 0; y < input_i ; y++){
				if(x == y || y == input_i-decreaser){
					sLine+="*";
					System.out.print("*");
				}
				else{
					sLine+=" ";
					System.out.print(" ");	
				}
			}
			if(inputMap.containsKey(String.valueOf(x))){
				inputMap.put(String.valueOf(x), inputMap.get(String.valueOf(x))+" "+sLine);
			} else {
				inputMap.put(String.valueOf(x), sLine);
			}
			decreaser++;
			System.out.print("\n");
		}
	}
	
	public static void drawY(int input_i){
		int decreaser = 1;
		int middle = 0;
		boolean isDone = false;
		String sLine;
		for(int x = 0; x < input_i; x++){		
			sLine = "";
			for(int y = 0; y < input_i ; y++){
				if(middle != 0 && middle == y){
					sLine+="*";
					System.out.print("*");	
				}
				else if((x == y && y == input_i-decreaser) && !isDone){
					sLine+="*";
					middle = y;
					isDone = true;
					System.out.print("*");	
				}
				else if((x == y || y == input_i-decreaser) && !isDone){
					sLine+="*";
					System.out.print("*");
				}
				else{
					sLine+=" ";
					System.out.print(" ");	
				}
			}
			if(inputMap.containsKey(String.valueOf(x))){
				inputMap.put(String.valueOf(x), inputMap.get(String.valueOf(x))+" "+sLine);
			} else {
				inputMap.put(String.valueOf(x), sLine);
			}
			decreaser++;
			System.out.print("\n");
		}
	}
	
	public static boolean isNumeric(String str) {
		try {
			double d = Double.parseDouble(str);
		} catch (NumberFormatException nfe) {
			return false;
		}
		return true;
	}
	
	public static void verticalPrint(int input){
		System.out.println("===VERICAL===");
		System.out.print("\n");
        drawX(input);
        System.out.print("\n");
        drawY(input);
        System.out.print("\n");
        drawZ(input);
        System.out.print("\n");
	}
	
	public static void horizontalPrint(LinkedHashMap<String, Serializable> input_map){
        Set<Entry<String, Serializable>> entries = input_map.entrySet();
        System.out.println("===HORIZONTAL===");
        System.out.print("\n");
        for(Entry<String, Serializable> ent: entries){
       		System.out.println(ent.getValue());
        }
	}

}
